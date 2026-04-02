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
    $servername = "localhost";
    $dbusername = "firstcit_dev";
    $dbpassword = "e3QEKMJ2w8kLa7yZSfgj";
    $dbname = "firstcit_db";
    
    $dbconn = mysqli_connect($servername, $dbusername, $dbpassword, $dbname);
    
    if (!$dbconn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    
    return $dbconn;
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
?>