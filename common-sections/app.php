<?php
// Setting initials
// rsend api re_6UXBpV3q_Ee83gTNZod4QexanZjZh9Ss8
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
date_default_timezone_set('America/New_York');




// Database connection function
function connectToDatabase() {
    $servername = getenv('DB_HOST') ?: '127.0.0.1';
    $dbusername = getenv('DB_USER') ?: 'bzvgbkjtlx_user';
    $dbpassword = getenv('DB_PASS') ?: 'Wateva06@';
    $dbname = getenv('DB_NAME') ?: 'bzvgbkjtlx_db';
    
    $dbconn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
    
    if (!$dbconn) {
        die("Database connection failed. Please verify database configuration.");
    }

    mysqli_set_charset($dbconn, 'utf8mb4');
    
    return $dbconn;
}





// Dynamic contact details
define('DEFAULT_SUPPORT_PHONE', '+17252885411');

function normalizePhoneForWhatsapp(string $phone): string {
    $digits = preg_replace('/\D+/', '', $phone);
    return $digits ?: preg_replace('/\D+/', '', DEFAULT_SUPPORT_PHONE);
}

function getSupportPhoneNumber(): string {
    static $cachedPhone = null;

    if ($cachedPhone !== null) {
        return $cachedPhone;
    }

    $dbconn = connectToDatabase();
    $phone = DEFAULT_SUPPORT_PHONE;

    $query = "SELECT `value` FROM dynamic_data WHERE `name` = 'phone_number' LIMIT 1";
    $result = mysqli_query($dbconn, $query);

    if ($result && ($row = mysqli_fetch_assoc($result))) {
        $value = trim((string) ($row['value'] ?? ''));
        if ($value !== '') {
            $phone = $value;
        }
    }

    mysqli_close($dbconn);
    $cachedPhone = $phone;

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
