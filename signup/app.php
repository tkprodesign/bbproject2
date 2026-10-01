<?php
require_once __DIR__ . '/../common-sections/app.php';

// Form handler for alert information section
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

// Form handler for user sign-up
if (isset($_POST['sign_up'])) {
    $dbconn = connectToDatabase();

    // Sanitize user inputs
    $name = mysqli_real_escape_string($dbconn, $_POST['full_name']);
    $email = mysqli_real_escape_string($dbconn, $_POST['email']);
    $password = mysqli_real_escape_string($dbconn, $_POST['password']);

    // Check if user already exists in the database
    $table = 'users';
    if (isInTable($email, $table)) {
        $_GET['alert_time'] = time();
        $_GET['alert_info_section'] = 
        '<section class="alert-info">
            <div class="container">
                <span>User Already Exists, Login With Your Registered Email Address.</span>
            </div>
        </section>'; 
    } else {
        // Prepare and insert user into the database
        $password = password_hash($password, PASSWORD_DEFAULT);
        $time = time();
        $date_registered = $time;
        $human_time = date('H:i | d/m/Y', $time) . ' | New York Time';

        $sql = "INSERT INTO users (name, email, password, date_registered, human_time) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $dbconn->prepare($sql);

        // Bind parameters and execute statement
        $stmt->bind_param('sssis', $name, $email, $password, $date_registered, $human_time);
        $stmt->execute();
        $newUserId = (int)$dbconn->insert_id;
        $stmt->close();

        $customerNumber = 'VLM-' . str_pad((string)$newUserId, 8, '0', STR_PAD_LEFT);
        try {
            $metaStmt = $dbconn->prepare("UPDATE users SET customer_number = ?, user_status = 'Active' WHERE id = ?");
            if ($metaStmt) {
                $metaStmt->bind_param('si', $customerNumber, $newUserId);
                $metaStmt->execute();
                $metaStmt->close();
            }

            $prefStmt = $dbconn->prepare("INSERT IGNORE INTO user_preferences (user_email) VALUES (?)");
            if ($prefStmt) {
                $prefStmt->bind_param('s', $email);
                $prefStmt->execute();
                $prefStmt->close();
            }

            $eventType = 'Registration';
            $eventDescription = 'Online banking profile created';
            $registrationIp = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
            $registrationAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);
            $eventStmt = $dbconn->prepare("INSERT INTO security_events (user_email, event_type, description, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
            if ($eventStmt) {
                $eventStmt->bind_param('sssss', $email, $eventType, $eventDescription, $registrationIp, $registrationAgent);
                $eventStmt->execute();
                $eventStmt->close();
            }
        } catch (Throwable $e) {
            error_log('Registration metadata tracking skipped: ' . $e->getMessage());
        }

        $dbconn->close();

        // Stay on signup page after successful registration
        header('location: /signup/?registered=true');
        exit;
    }
}

if (isset($_GET['registered']) && $_GET['registered'] === 'true') {
    $_GET['alert_info_section'] =
    '<section class="alert-info">
        <div class="container">
            <span>Account created successfully. You can now <a href="/login/">sign in</a>.</span>
        </div>
    </section>';
}
?>
