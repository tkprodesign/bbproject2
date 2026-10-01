<?php
// Setting initials
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}
$velmoraDebug = strtolower((string)(getenv('APP_ENV') ?: 'production')) === 'development';
ini_set('display_errors', $velmoraDebug ? '1' : '0');
ini_set('display_startup_errors', $velmoraDebug ? '1' : '0');
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');

require_once __DIR__ . '/fx.php';




// Database connection function
// Live deployment root verified by workflow.
function connectToDatabase() {
    // Production credentials live outside the public web root in a JSON file.
    // Reading JSON each request avoids stale PHP/opcache values after credential updates.
    $cfg = [];
    $secretConfigFile = dirname(__DIR__, 2) . '/.velmora-db.json';
    if (is_file($secretConfigFile)) {
        $decoded = json_decode((string) file_get_contents($secretConfigFile), true);
        if (is_array($decoded)) {
            $cfg = $decoded;
        }
    }

    // Legacy fallback for local/dev or older deployments.
    if (!$cfg) {
        $legacyConfigFile = __DIR__ . '/../db-config.php';
        if (!defined('DB_CONFIG') && file_exists($legacyConfigFile)) {
            require_once $legacyConfigFile;
        }
        $cfg = defined('DB_CONFIG') ? DB_CONFIG : [];
    }

    $socket     = ($cfg['socket'] ?? '') ?: getenv('DB_SOCKET');
    $host       = ($cfg['host'] ?? '') ?: (getenv('DB_HOST') ?: 'localhost');
    $port       = (int)(($cfg['port'] ?? 0) ?: (getenv('DB_PORT') ?: 3306));
    $dbusername = ($cfg['user'] ?? '') ?: getenv('DB_USER');
    $dbpassword = ($cfg['password'] ?? '') ?: getenv('DB_PASS');
    $dbname     = ($cfg['name'] ?? '') ?: getenv('DB_NAME');

    // Prefer Unix socket when socket file exists (null host triggers socket mode)
    if ($socket && file_exists($socket)) {
        $dbconn = new mysqli(null, $dbusername, $dbpassword, $dbname, null, $socket);
    } else {
        $dbconn = new mysqli($host, $dbusername, $dbpassword, $dbname, $port);
    }

    if ($dbconn->connect_error) {
        die("Database connection failed: " . $dbconn->connect_error);
    }

    $dbconn->set_charset('utf8mb4');

    return $dbconn;
}





// Dynamic contact details
define('DEFAULT_SUPPORT_PHONE', '+17252885411');

function getDefaultDynamicData(): array {
    return [
        'phone_number' => DEFAULT_SUPPORT_PHONE,
        'btc_address' => '',
        'eth_address' => '',
        'usdt_address' => '',
        'doge_address' => '',
    ];
}

function ensureDynamicDataTable(mysqli $dbconn): bool {
    static $checked = false;

    if ($checked) {
        return true;
    }

    try {
        $dbconn->query("CREATE TABLE IF NOT EXISTS dynamic_data (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            value TEXT DEFAULT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $seedStmt = $dbconn->prepare('INSERT IGNORE INTO dynamic_data (`name`, `value`) VALUES (?, ?)');
        if ($seedStmt) {
            foreach (getDefaultDynamicData() as $name => $value) {
                $seedStmt->bind_param('ss', $name, $value);
                $seedStmt->execute();
            }
            $seedStmt->close();
        }
    } catch (mysqli_sql_exception $exception) {
        error_log('Unable to initialize dynamic_data table: ' . $exception->getMessage());
        return false;
    }

    $checked = true;
    return true;
}

function getDynamicDataValue(string $name, string $default = ''): string {
    $dbconn = connectToDatabase();
    $value = $default;

    try {
        if (ensureDynamicDataTable($dbconn)) {
            $stmt = $dbconn->prepare('SELECT `value` FROM dynamic_data WHERE `name` = ? LIMIT 1');
            if ($stmt) {
                $stmt->bind_param('s', $name);
                $stmt->execute();
                $result = $stmt->get_result();
                if ($result && ($row = $result->fetch_assoc())) {
                    $dbValue = trim((string) ($row['value'] ?? ''));
                    if ($dbValue !== '') {
                        $value = $dbValue;
                    }
                }
                $stmt->close();
            }
        }
    } catch (mysqli_sql_exception $exception) {
        error_log('Unable to read dynamic data value "' . $name . '": ' . $exception->getMessage());
    }

    $dbconn->close();
    return $value;
}

function normalizePhoneForWhatsapp(string $phone): string {
    $digits = preg_replace('/\D+/', '', $phone);
    return $digits ?: preg_replace('/\D+/', '', DEFAULT_SUPPORT_PHONE);
}

function getSupportPhoneNumber(): string {
    static $cachedPhone = null;

    if ($cachedPhone === null) {
        $cachedPhone = getDynamicDataValue('phone_number', DEFAULT_SUPPORT_PHONE);
    }

    return $cachedPhone;
}

function getSupportWhatsappLink(): string {
    return 'https://wa.me/' . normalizePhoneForWhatsapp(getSupportPhoneNumber());
}

function loadPHPMailerClasses(): bool {
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return true;
    }

    $phpMailerPath = __DIR__ . '/../PHPMailer/src/';
    if (!file_exists($phpMailerPath . 'PHPMailer.php')) {
        error_log('PHPMailer library was not found at ' . $phpMailerPath);
        return false;
    }

    require_once $phpMailerPath . 'PHPMailer.php';
    require_once $phpMailerPath . 'SMTP.php';
    require_once $phpMailerPath . 'Exception.php';

    return class_exists('PHPMailer\PHPMailer\PHPMailer');
}

function getEmailPasswordForSender(string $fromEmail): string {
    $passwordsBySender = [
        'support@velmorabank.us' => getenv('SUPPORT_EMAIL_PASSWORD') ?: '',
        'admin@velmorabank.us' => getenv('ADMIN_EMAIL_PASSWORD') ?: '',
        'no-reply@velmorabank.us' => getenv('NOREPLY_EMAIL_PASSWORD') ?: '',
    ];

    return getenv('SMTP_PASSWORD') ?: ($passwordsBySender[strtolower($fromEmail)] ?? '');
}

function getSecurityNoticeSender(): array {
    if ((string)(getenv('RESEND_API_KEY') ?: '') !== '') {
        return ['email' => 'security@velmorabank.us', 'name' => 'Velmora Bank Security'];
    }
    return ['email' => 'support@velmorabank.us', 'name' => 'Velmora Bank Support'];
}

function sendSiteEmailViaResend(string $to, string $subject, string $htmlBody, string $fromEmail, string $fromName): bool {
    $apiKey = trim((string)(getenv('RESEND_API_KEY') ?: ''));
    if ($apiKey === '' || !function_exists('curl_init')) {
        return false;
    }

    $fromEmail = strtolower(trim($fromEmail));
    $allowedSenders = [
        'support@velmorabank.us',
        'security@velmorabank.us',
        'no-reply@velmorabank.us',
        'admin@velmorabank.us',
    ];
    if (!in_array($fromEmail, $allowedSenders, true)) {
        error_log('Resend blocked an unapproved sender identity: '.$fromEmail);
        return false;
    }

    $replyTo = $fromEmail === 'no-reply@velmorabank.us'
        ? 'support@velmorabank.us'
        : $fromEmail;

    $payload = [
        'from' => trim($fromName).' <'.$fromEmail.'>',
        'to' => [$to],
        'subject' => $subject,
        'html' => $htmlBody,
        'text' => trim(html_entity_decode(strip_tags($htmlBody), ENT_QUOTES, 'UTF-8')),
        'reply_to' => $replyTo,
    ];

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer '.$apiKey,
            'Content-Type: application/json',
        ],
        CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE),
    ]);

    $response = curl_exec($ch);
    $statusCode = (int)curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($response !== false && $statusCode >= 200 && $statusCode < 300) {
        return true;
    }

    error_log('Resend email failed for '.$fromEmail.' with HTTP '.$statusCode.($curlError !== '' ? ': '.$curlError : ''));
    return false;
}

function sendSiteEmailViaSmtp(string $to, string $subject, string $htmlBody, string $fromEmail, string $fromName): bool {
    if (!loadPHPMailerClasses()) {
        return false;
    }

    $smtpHost = getenv('SMTP_HOST') ?: 'mail.spacemail.com';
    $smtpPort = (int)(getenv('SMTP_PORT') ?: 465);
    $smtpUser = getenv('SMTP_USERNAME') ?: $fromEmail;
    $smtpPassword = getEmailPasswordForSender($fromEmail);
    $smtpEncryption = strtolower(getenv('SMTP_ENCRYPTION') ?: ($smtpPort === 465 ? 'ssl' : 'tls'));

    if ($smtpPassword === '') {
        return false;
    }

    $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host = $smtpHost;
        $mail->SMTPAuth = true;
        $mail->Username = $smtpUser;
        $mail->Password = $smtpPassword;
        $mail->Port = $smtpPort;
        $mail->SMTPSecure = $smtpEncryption === 'ssl'
            ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
            : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom($fromEmail, $fromName);
        $mail->addAddress($to);
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $htmlBody;
        $mail->AltBody = trim(html_entity_decode(strip_tags($htmlBody), ENT_QUOTES, 'UTF-8'));

        return $mail->send();
    } catch (\PHPMailer\PHPMailer\Exception $exception) {
        error_log('SMTP email failed: '.$exception->getMessage());
        return false;
    }
}

function sendSiteEmail(string $to, string $subject, string $htmlBody, string $fromEmail = 'no-reply@velmorabank.us', string $fromName = 'Velmora Bank Notifications'): bool {
    if ((string)(getenv('RESEND_API_KEY') ?: '') !== '') {
        if (sendSiteEmailViaResend($to, $subject, $htmlBody, $fromEmail, $fromName)) {
            return true;
        }
        error_log('Resend delivery failed; attempting SpaceMail SMTP fallback.');
    }

    // SpaceMail is the fallback transport. With the current single-mailbox plan,
    // aliases should use Resend; support@ remains the physical mailbox.
    $smtpFromEmail = strtolower($fromEmail) === 'support@velmorabank.us'
        ? 'support@velmorabank.us'
        : 'support@velmorabank.us';
    $smtpFromName = strtolower($fromEmail) === 'support@velmorabank.us'
        ? $fromName
        : 'Velmora Bank Support';

    return sendSiteEmailViaSmtp($to, $subject, $htmlBody, $smtpFromEmail, $smtpFromName);
}


function createUserNotification(mysqli $db, string $email, string $title, string $body, string $type = 'General', ?string $actionUrl = null): void {
    try {
        $stmt=$db->prepare("SELECT in_app_notifications FROM user_preferences WHERE user_email=? LIMIT 1");
        if($stmt){
            $stmt->bind_param('s',$email);$stmt->execute();$stmt->bind_result($enabled);
            if($stmt->fetch() && (int)$enabled===0){$stmt->close();return;}
            $stmt->close();
        }
        $stmt=$db->prepare("INSERT INTO notifications (user_email,title,body,notification_type,action_url) VALUES (?,?,?,?,?)");
        if($stmt){$stmt->bind_param('sssss',$email,$title,$body,$type,$actionUrl);$stmt->execute();$stmt->close();}
    } catch(Throwable $e){ error_log('Notification record skipped: '.$e->getMessage()); }
}

function recordSecurityEvent(mysqli $db, string $email, string $eventType, string $description): void {
    try {
        $ip=substr((string)($_SERVER['REMOTE_ADDR']??''),0,45);
        $ua=substr((string)($_SERVER['HTTP_USER_AGENT']??''),0,500);
        $stmt=$db->prepare("INSERT INTO security_events (user_email,event_type,description,ip_address,user_agent) VALUES (?,?,?,?,?)");
        if($stmt){$stmt->bind_param('sssss',$email,$eventType,$description,$ip,$ua);$stmt->execute();$stmt->close();}
    } catch(Throwable $e){ error_log('Security event record skipped: '.$e->getMessage()); }
}


function velmoraRevokeCustomerSessions(mysqli $db, string $email): int {
    $email = strtolower(trim($email));
    $stmt = $db->prepare('UPDATE users SET session_version=GREATEST(1,session_version+1) WHERE email=?');
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();
    }
    $stmt = $db->prepare('DELETE FROM customer_remember_tokens WHERE user_email=?');
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();
    }
    $version = 1;
    $stmt = $db->prepare('SELECT session_version FROM users WHERE email=? LIMIT 1');
    if ($stmt) {
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($version);
        $stmt->fetch();
        $stmt->close();
    }
    return max(1, (int)$version);
}

function velmoraCustomerCookieSecure(): bool {
    return !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
}

function velmoraExpireCookie(string $name): void {
    setcookie($name, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => velmoraCustomerCookieSecure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    unset($_COOKIE[$name]);
}

function velmoraDeleteRememberTokenByCookie(): void {
    $cookie = (string)($_COOKIE['vlm_remember'] ?? '');
    $parts = explode(':', $cookie, 2);
    if (count($parts) === 2 && preg_match('/^[a-f0-9]{32}$/', $parts[0])) {
        try {
            $db = connectToDatabase();
            $stmt = $db->prepare('DELETE FROM customer_remember_tokens WHERE selector=?');
            if ($stmt) {
                $stmt->bind_param('s', $parts[0]);
                $stmt->execute();
                $stmt->close();
            }
            $db->close();
        } catch (Throwable $e) {
            error_log('Remember-token cleanup skipped: '.$e->getMessage());
        }
    }
    velmoraExpireCookie('vlm_remember');
}

function velmoraLogoutCustomer(bool $deleteRememberToken = true): void {
    if ($deleteRememberToken) {
        velmoraDeleteRememberTokenByCookie();
    } else {
        velmoraExpireCookie('vlm_remember');
    }
    velmoraExpireCookie('login_email');
    unset($_SESSION['customer_auth'], $_SESSION['user_email'], $_SESSION['login_email']);
    unset($GLOBALS['velmora_customer_auth_cache']);
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

function velmoraEstablishCustomerSession(string $email, int $sessionVersion): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
    $email = strtolower(trim($email));
    $_SESSION['customer_auth'] = [
        'email' => $email,
        'session_version' => max(1, $sessionVersion),
        'authenticated_at' => time(),
    ];
    // Compatibility for existing dashboard code; this value is now server-side only.
    $_SESSION['user_email'] = $email;
    unset($_SESSION['login_email']);
    velmoraExpireCookie('login_email');
    unset($GLOBALS['velmora_customer_auth_cache']);
}

function velmoraIssueRememberToken(mysqli $db, string $email, int $sessionVersion, int $days = 30): void {
    $selector = bin2hex(random_bytes(16));
    $validator = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $validator);
    $expiresTs = time() + max(1, $days) * 86400;
    $expiresAt = date('Y-m-d H:i:s', $expiresTs);

    $cleanup = $db->prepare('DELETE FROM customer_remember_tokens WHERE user_email=? OR expires_at<NOW()');
    if ($cleanup) {
        $cleanup->bind_param('s', $email);
        $cleanup->execute();
        $cleanup->close();
    }

    $stmt = $db->prepare('INSERT INTO customer_remember_tokens (selector,token_hash,user_email,session_version,expires_at) VALUES (?,?,?,?,?)');
    if (!$stmt) {
        throw new RuntimeException('Unable to prepare remember token.');
    }
    $stmt->bind_param('sssis', $selector, $tokenHash, $email, $sessionVersion, $expiresAt);
    $stmt->execute();
    $stmt->close();

    $value = $selector.':'.$validator;
    setcookie('vlm_remember', $value, [
        'expires' => $expiresTs,
        'path' => '/',
        'secure' => velmoraCustomerCookieSecure(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    $_COOKIE['vlm_remember'] = $value;
}

function velmoraResolveCustomerAuth(): array {
    if (isset($GLOBALS['velmora_customer_auth_cache']) && is_array($GLOBALS['velmora_customer_auth_cache'])) {
        return $GLOBALS['velmora_customer_auth_cache'];
    }

    $email = '';
    $sessionVersion = 0;
    $source = 'none';

    $sessionAuth = $_SESSION['customer_auth'] ?? null;
    if (is_array($sessionAuth)) {
        $email = strtolower(trim((string)($sessionAuth['email'] ?? '')));
        $sessionVersion = (int)($sessionAuth['session_version'] ?? 0);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && $sessionVersion > 0) {
            $source = 'session';
        } else {
            $email = '';
            $sessionVersion = 0;
        }
    }

    $db = null;
    if ($email === '') {
        $cookie = (string)($_COOKIE['vlm_remember'] ?? '');
        $parts = explode(':', $cookie, 2);
        if (count($parts) === 2 && preg_match('/^[a-f0-9]{32}$/', $parts[0]) && preg_match('/^[a-f0-9]{64}$/', $parts[1])) {
            try {
                $db = connectToDatabase();
                $stmt = $db->prepare("SELECT t.user_email,t.token_hash,t.session_version,u.user_status,u.session_version AS current_version
                                      FROM customer_remember_tokens t
                                      JOIN users u ON u.email=t.user_email
                                      WHERE t.selector=? AND t.expires_at>NOW() LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('s', $parts[0]);
                    $stmt->execute();
                    $row = $stmt->get_result()->fetch_assoc();
                    $stmt->close();
                    if ($row && hash_equals((string)$row['token_hash'], hash('sha256', $parts[1]))) {
                        $status = strtolower(trim((string)$row['user_status']));
                        if (in_array($status, ['active','enabled'], true) && (int)$row['session_version'] === (int)$row['current_version']) {
                            $email = strtolower((string)$row['user_email']);
                            $sessionVersion = (int)$row['current_version'];
                            $source = 'remember';
                            velmoraEstablishCustomerSession($email, $sessionVersion);
                            $touch = $db->prepare('UPDATE customer_remember_tokens SET last_used_at=NOW() WHERE selector=?');
                            if ($touch) {$touch->bind_param('s',$parts[0]);$touch->execute();$touch->close();}
                        } else {
                            $GLOBALS['velmora_customer_auth_cache'] = ['authenticated'=>false,'email'=>'','status'=>(string)$row['user_status'],'source'=>'remember'];
                            $delete = $db->prepare('DELETE FROM customer_remember_tokens WHERE selector=?');
                            if ($delete) {$delete->bind_param('s',$parts[0]);$delete->execute();$delete->close();}
                            $db->close();
                            velmoraExpireCookie('vlm_remember');
                            return $GLOBALS['velmora_customer_auth_cache'];
                        }
                    } else {
                        velmoraExpireCookie('vlm_remember');
                    }
                }
            } catch (Throwable $e) {
                error_log('Remember-token authentication skipped: '.$e->getMessage());
            }
        }
    }

    if ($email === '') {
        if ($db instanceof mysqli) {$db->close();}
        return $GLOBALS['velmora_customer_auth_cache'] = ['authenticated'=>false,'email'=>'','status'=>'','source'=>'none'];
    }

    try {
        if (!$db instanceof mysqli) {$db = connectToDatabase();}
        $stmt = $db->prepare('SELECT user_status,session_version FROM users WHERE email=? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        $db->close();

        if (!$row) {
            velmoraLogoutCustomer($source === 'remember');
            return $GLOBALS['velmora_customer_auth_cache'] = ['authenticated'=>false,'email'=>'','status'=>'','source'=>$source];
        }

        $status = trim((string)$row['user_status']);
        $currentVersion = (int)$row['session_version'];
        if (!in_array(strtolower($status), ['active','enabled'], true) || $currentVersion !== $sessionVersion) {
            velmoraLogoutCustomer($source === 'remember');
            return $GLOBALS['velmora_customer_auth_cache'] = ['authenticated'=>false,'email'=>'','status'=>$status,'source'=>$source];
        }

        return $GLOBALS['velmora_customer_auth_cache'] = [
            'authenticated'=>true,
            'email'=>$email,
            'status'=>$status,
            'session_version'=>$currentVersion,
            'source'=>$source,
        ];
    } catch (Throwable $e) {
        if ($db instanceof mysqli) {$db->close();}
        error_log('Customer authentication check failed: '.$e->getMessage());
        return $GLOBALS['velmora_customer_auth_cache'] = ['authenticated'=>false,'email'=>'','status'=>'','source'=>$source];
    }
}

function velmoraCurrentCustomerEmail(): ?string {
    $auth = velmoraResolveCustomerAuth();
    return !empty($auth['authenticated']) ? (string)$auth['email'] : null;
}

//Check for item in database
function isInTable($email, $table) {
    $dbconn = connectToDatabase();

    // Validate table names to avoid SQL injection
    $allowedTables = ['users']; // List of allowed tables
    if (!in_array($table, $allowedTables)) {
        die("Invalid table name.");
    }

    // Prepare the SQL statement to prevent SQL injection
    $stmt = $dbconn->prepare("SELECT COUNT(*) FROM $table WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    $dbconn->close();

    return $count > 0;
}

function normalizeLegacyTransactionStatuses(): void {
    static $normalized = false;
    if ($normalized) {
        return;
    }

    $dbconn = connectToDatabase();
    $stmt = $dbconn->prepare("UPDATE transactions SET status = 'Successful' WHERE LOWER(status) = 'completed'");
    if ($stmt) {
        $stmt->execute();
        $stmt->close();
    }
    $dbconn->close();
    $normalized = true;
}



// Restrict access to internal pages when the visitor is not logged in.
function requireLoginForInternalPages() {
    if (php_sapi_name() === 'cli') {
        return;
    }

    $requestUri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($requestUri, PHP_URL_PATH) ?: '/';
    $normalizedPath = rtrim($path, '/');
    if ($normalizedPath === '') {
        $normalizedPath = '/';
    }

    // Exact public paths
    $publicPaths = [
        '/',
        '/index.php',
    ];

    // Public path prefixes — any URL starting with these is accessible without login
    $publicPrefixes = [
        '/backend-login',
        '/backend-logout',
        '/control-panel',
        '/support-control-panel',
        '/master-control-panel',
        '/login',
        '/login-v2',
        '/signup',
        '/signup-v2',
        '/onboarding-v2',
        '/international',
        '/support',
        '/security',
        '/rates',
        '/documents',
        '/legal',
        '/privacy',
        '/terms',
        '/accessibility',
        '/onboarding',
        '/forgot-password',
        '/reset-password',
        '/404',
        '/not-found-v2',
        '/reset-password-v2',
        '/forgot-password-v2',
        '/sign-up',
        '/about-us',
        '/personal',
        '/business',
        '/credit-card',
        '/loan',
        '/contact',
        '/careers',
        '/atm-and-bank-locations',
        '/quick-links',
        '/online-banking',
        '/home-v2',
        '/personal-v2',
        '/business-v2',
        '/credit-card-v2',
        '/loan-v2',
        '/online-banking-v2',
        '/international-v2',
        '/about-v2',
        '/contact-v2',
        '/locations-v2',
        '/careers-v2',
        '/support-v2',
        '/security-v2',
        '/rates-v2',
        '/documents-v2',
        '/legal-v2',
        '/privacy-v2',
        '/terms-v2',
        '/cookie-policy-v2',
        '/accessibility-v2',
        '/cookie-policy',
        '/assets',
    ];

    if (in_array($normalizedPath, $publicPaths, true)) {
        return;
    }

    foreach ($publicPrefixes as $prefix) {
        if ($normalizedPath === $prefix || str_starts_with($normalizedPath, $prefix . '/')) {
            return;
        }
    }

    $auth = velmoraResolveCustomerAuth();
    if (empty($auth['authenticated'])) {
        $suffix = in_array(strtolower((string)($auth['status'] ?? '')), ['restricted','archived','suspended'], true) ? '?restricted=yes' : '';
        header('Location: /login/' . $suffix);
        exit;
    }
}

requireLoginForInternalPages();

?>
