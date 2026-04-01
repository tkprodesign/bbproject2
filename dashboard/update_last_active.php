<?php
session_start();
if (isset($_SESSION['user_email'])) {
    $sesion_email = $_SESSION['user_email'];
    $dbconn = connectToDatabase();

    $time = time();
    $sql = "UPDATE users SET last_active = ? WHERE email = ?";
    $stmt = $dbconn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param('is', $time, $sesion_email);
        $stmt->execute();
        $stmt->close();
    }

    $dbconn->close();
} 
?>