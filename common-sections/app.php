<?php
// Setting initials
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');




// Database connection function
function connectToDatabase() {
    // Load production config file if present (used on shared hosting with no env vars)
    $configFile = __DIR__ . '/../db-config.php';
    if (!defined('DB_CONFIG') && file_exists($configFile)) {
        require_once $configFile;
    }
    $cfg = defined('DB_CONFIG') ? DB_CONFIG : [];

    $socket     = getenv('DB_SOCKET') ?: ($cfg['socket'] ?? '');
    $host       = getenv('DB_HOST')   ?: ($cfg['host']   ?? 'localhost');
    $port       = (int)(getenv('DB_PORT') ?: ($cfg['port'] ?? 3306));
    $dbusername = getenv('DB_USER')   ?: ($cfg['user']   ?? '');
    $dbpassword = getenv('DB_PASS')   ?: ($cfg['password'] ?? '');
    $dbname     = getenv('DB_NAME')   ?: ($cfg['name']   ?? '');

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

    $publicPaths = [
        '/',
        '/index.php',
        '/create_tables',
        '/create_tables.php',
        '/login',
        '/login/index.php',
        '/signup',
        '/signup/',
        '/signup/index.php',
        '/sign-up',
        '/sign-up/',
        '/sign-up/index.php',
    ];

    if (!in_array($normalizedPath, $publicPaths, true) && !isset($_COOKIE['login_email'])) {
        header('Location: /login');
        exit;
    }
}

requireLoginForInternalPages();

?>
