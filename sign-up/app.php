<?php
// Include app.php based on its location in the directory structure
if (file_exists('../common-sections/app.php')) {
    require '../common-sections/app.php';
} elseif (file_exists('../../common-sections/app.php')) {
    require '../../common-sections/app.php';
} else {
    require '../../../common-sections/app.php';
}

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
        $stmt->close();
        $dbconn->close();

        // Redirect to login page after successful registration
        header('location: /login?signup=true');
    }
}
?>
