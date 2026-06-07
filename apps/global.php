<?php
//---CONTENTS--
/*
1. Start session, error reporting, setting timezone
2. Require PHPMailer to the option of 6 directory levels down
3. Database connection function
4. Declaring and setting up the, site email variables, domain name variable, site link variable, partial site link variable, email host link variable.
5. Retrieving admin dynamic data from database: Phone number, btc address, eth adress, usdt address, doge address.
6. Setting logout function
*/
// Start session and error reporting
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');






// PHP Mailer
$paths = [
    '../PHPMailer/src/',
    '../../PHPMailer/src/',
    '../../../PHPMailer/src/',
    '../../../../PHPMailer/src/',
    '../../../../../PHPMailer/src/',
    '../../../../../../PHPMailer/src/'
];

foreach ($paths as $path) {
    if (file_exists($path . 'PHPMailer.php')) {
        require $path . 'PHPMailer.php';
        require $path . 'SMTP.php';
        require $path . 'Exception.php';
        break;
    }
}








// Database connection function
function connectToDatabase() {
    // Load production config file if present (used on shared hosting with no env vars)
    $configFile = __DIR__ . '/../db-config.php';
    if (!defined('DB_CONFIG') && file_exists($configFile)) {
        require_once $configFile;
    }
    $cfg = defined('DB_CONFIG') ? DB_CONFIG : [];

    $socket   = getenv('DB_SOCKET') ?: ($cfg['socket'] ?? '');
    $host     = getenv('DB_HOST')   ?: ($cfg['host']   ?? 'localhost');
    $port     = (int)(getenv('DB_PORT') ?: ($cfg['port'] ?? 3306));
    $username = getenv('DB_USER')   ?: ($cfg['user']   ?? '');
    $password = getenv('DB_PASS')   ?: ($cfg['password'] ?? '');
    $dbname   = getenv('DB_NAME')   ?: ($cfg['name']   ?? '');

    // Use null host to trigger Unix socket mode in mysqli
    if ($socket && file_exists($socket)) {
        $dbconn = new mysqli(null, $username, $password, $dbname, null, $socket);
    } else {
        $dbconn = new mysqli($host, $username, $password, $dbname, $port);
    }

    if ($dbconn->connect_error) {
        die("Connection failed: " . $dbconn->connect_error);
    }
    $dbconn->set_charset('utf8mb4');
    return $dbconn;
}

function getDefaultDynamicData(): array {
    return [
        'phone_number' => '+17252885411',
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

function getDynamicDataValue(mysqli $dbconn, string $name, string $default = ''): string {
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

    return $value;
}





//Emails
$adminEmail = 'admin@velmorabank.us';
$adminEmailPassword = getenv('ADMIN_EMAIL_PASSWORD') ?: '';

$supportEmail = 'support@velmorabank.us';
$supportEmailPassword = getenv('SUPPORT_EMAIL_PASSWORD') ?: '';

$autoEmail = 'no-reply@velmorabank.us';
$autoEmailPassword = getenv('NOREPLY_EMAIL_PASSWORD') ?: '';

$byepassEmail = 'itekena.s.iyowuna@gmail.co';

$domain = 'velmorabank.us';
$fullLink = 'https://velmorabank.us';
$partialLink = 'velmorabank.us';
$emailHost = 'mail.velmorabank.us';







//Admin dynamic data (storage created for admin to change site wallet addresses and phone number at will)
$dbconn = connectToDatabase();
$dynamicDataDefaults = getDefaultDynamicData();

$phone_number = getDynamicDataValue($dbconn, 'phone_number', $dynamicDataDefaults['phone_number']);
$btc_address = getDynamicDataValue($dbconn, 'btc_address', $dynamicDataDefaults['btc_address']);
$eth_address = getDynamicDataValue($dbconn, 'eth_address', $dynamicDataDefaults['eth_address']);
$usdt_address = getDynamicDataValue($dbconn, 'usdt_address', $dynamicDataDefaults['usdt_address']);
$doge_address = getDynamicDataValue($dbconn, 'doge_address', $dynamicDataDefaults['doge_address']);

$dbconn->close();




//Logout function
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    // Destroy the user_email cookie
    if (isset($_COOKIE['user_email'])) {
        setcookie('user_email', '', time() - 3600, '/'); // Set cookie expiration time in the past
    }

    // Unset session variable
    if (isset($_SESSION['user_email'])) {
        unset($_SESSION['user_email']);
    }

    // Destroy the session
    session_destroy();

    // Redirect to the login page
    header("Location: /users/login");
    exit();
}
?>