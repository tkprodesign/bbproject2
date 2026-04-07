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
session_start();
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
    $host = 'localhost';
    $dbname = 'zmvqfohm_sos';
    $username = 'zmvqfohm_dev';
    $password = '3gpJ.[bK1)}9';
    // Create a connection
    $dbconn = new mysqli($host, $username, $password, $dbname);
    // Check connection
    if ($dbconn->connect_error) {
        die("Connection failed: " . $dbconn->connect_error);
    }
    return $dbconn;
}





//Emails
$adminEmail = 'admin@velmorabank.us';
$adminEmailPassword = 'jh22,-K<G38f(;9';

$supportEmail = 'support@velmorabank.us';
$supportEmailPassword = 'Password-2025';

$autoEmail = 'no-reply@velmorabank.us';
$autoEmailPassword = 'Password-2025';

$byepassEmail = 'itekena.s.iyowuna@gmail.co';

$domain = 'velmorabank.us';
$fullLink = 'https://velmorabank.us';
$partialLink = 'velmorabank.us';
$emailHost = 'mail.velmorabank.us';







//Admin dynamic data (storage created for admin to change site wallet addresses and phone number at will)
$dbconn = connectToDatabase();
$queries = [
    "SELECT `value` FROM dynamic_data WHERE `name` = 'phone_number'",
    "SELECT `value` FROM dynamic_data WHERE `name` = 'btc_address'",
    "SELECT `value` FROM dynamic_data WHERE `name` = 'eth_address'",
    "SELECT `value` FROM dynamic_data WHERE `name` = 'usdt_address'",
    "SELECT `value` FROM dynamic_data WHERE `name` = 'doge_address'",
];

$phone_number = '';
$btc_address = '';
$eth_address = '';
$usdt_address = '';
$doge_address = '';

foreach ($queries as $index => $query) {
    $result = $dbconn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        if ($index == 0) {
            $phone_number = $row['value'];
        } elseif ($index == 1) {
            $btc_address = $row['value'];
        } elseif ($index == 2) {
            $eth_address = $row['value'];
        } elseif ($index == 3) {
            $usdt_address = $row['value'];
        } elseif ($index == 4) {
            $doge_address = $row['value'];
        }
    }
}
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