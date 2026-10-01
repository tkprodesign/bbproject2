<?php
    require_once __DIR__ . '/../common-sections/app.php';

    // Handle alert info section based on time
    if (isset($_GET['alert_info_section'])) {
        $alert_time = $_GET['alert_time'];
        $time = time();

        if (($time - $alert_time) > 10) {
            $_GET['alert_info_section'] = '';
        } else {
            $_GET['alert_info_section'] = $_GET['alert_info_section'];
        }
    } else {
        $_GET['alert_info_section'] = '';
    }

    if (empty($_SESSION['customer_login_csrf'])) {
        $_SESSION['customer_login_csrf'] = bin2hex(random_bytes(24));
    }

    // Form handler for sign-in
    if (isset($_POST['sign_in'])) {
        $csrf = (string)($_POST['csrf'] ?? '');
        if ($csrf === '' || !hash_equals((string)$_SESSION['customer_login_csrf'], $csrf)) {
            $_GET['error'] = 'yes';
        } else {
            $dbconn = connectToDatabase();
            $email = strtolower(trim((string)($_POST['email'] ?? '')));
            $password = (string)($_POST['password'] ?? '');
            $rememberMe = isset($_POST['remember_me']) && (string)$_POST['remember_me'] === '1';

            $user = null;
            if (filter_var($email, FILTER_VALIDATE_EMAIL) && $password !== '') {
                $stmt = $dbconn->prepare("SELECT password,user_status,session_version FROM users WHERE email=? LIMIT 1");
                if ($stmt) {
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $user = $stmt->get_result()->fetch_assoc() ?: null;
                    $stmt->close();
                }
            }

            if (!$user || !password_verify($password, (string)$user['password'])) {
                $_GET['alert_time'] = time();
                $_GET['error'] = 'yes';
                $dbconn->close();
            } else {
                $status = trim((string)$user['user_status']);
                if (!in_array(strtolower($status), ['active','enabled'], true)) {
                    $_GET['alert_time'] = time();
                    $_GET['restricted'] = in_array(strtolower($status), ['restricted','archived','suspended'], true) ? 'yes' : '';
                    if ($_GET['restricted'] !== 'yes') {
                        $_GET['error'] = 'yes';
                    }
                    $dbconn->close();
                } else {
                    $sessionVersion = max(1, (int)$user['session_version']);
                    $loginTime = time();
                    $loginAt = date('Y-m-d H:i:s', $loginTime);
                    $loginIp = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
                    $userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);

                    try {
                        $trackStmt = $dbconn->prepare("UPDATE users SET last_active=?,last_login_at=?,last_login_ip=?,login_count=login_count+1 WHERE email=?");
                        if ($trackStmt) {
                            $trackStmt->bind_param('isss', $loginTime, $loginAt, $loginIp, $email);
                            $trackStmt->execute();
                            $trackStmt->close();
                        }
                        recordSecurityEvent($dbconn, $email, 'Sign In', 'Successful online banking sign-in');
                        velmoraEstablishCustomerSession($email, $sessionVersion);
                        if ($rememberMe) {
                            velmoraIssueRememberToken($dbconn, $email, $sessionVersion, 30);
                        } else {
                            velmoraDeleteRememberTokenByCookie();
                        }
                    } catch (Throwable $e) {
                        error_log('Customer sign-in setup failed: '.$e->getMessage());
                        velmoraLogoutCustomer(true);
                        $_GET['error'] = 'yes';
                        $dbconn->close();
                        return;
                    }

                    $dbconn->close();
                    $_SESSION['customer_login_csrf'] = bin2hex(random_bytes(24));
                    $loginTarget = defined('VELMORA_LOGIN_TARGET') ? VELMORA_LOGIN_TARGET : '/dashboard/';
                    header("Location: " . $loginTarget);
                    exit;
                }
            }
        }
    }

    // Show signup success message if account registration was successful
    if (isset($_GET['signup']) && $_GET['signup'] == 'true') {
        $_GET['alert_info_section'] = '
            <section class="alert-info">
                <div class="container">
                    <span>Account Successfully Registered, Login With Your Registered Email Address.</span>
                </div>
            </section>';
    }
?>
