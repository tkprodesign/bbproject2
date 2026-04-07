<?php
    // Include the common sections application file
    if (file_exists('../common-sections/app.php')) {
        require '../common-sections/app.php';
    } elseif (file_exists('../../common-sections/app.php')) {
        require '../../common-sections/app.php';
    } else {
        require '../../../common-sections/app.php';
    }

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


    $controlPanelAllowedEmails = [
        'tkprodesign96@gmail.com',
        'support@velmorabank.us',
        'admin@velmorabank.us',
    ];

    // Form handler for sign-in
    if (isset($_POST['sign_in'])) {
        $dbconn = connectToDatabase();

        // Sanitize input data
        $email = mysqli_real_escape_string($dbconn, $_POST['email']);
        $password = mysqli_real_escape_string($dbconn, $_POST['password']);
        $remember_me = isset($_POST['remember_me']) ? mysqli_real_escape_string($dbconn, $_POST['remember_me']) : 0;

        $table = 'users';

        // Check if the email exists in the table
        if (!isInTable($email, $table)) {
            $_GET['alert_time'] = time();
            $_GET['error'] = 'yes';
        } else {
            // Query to retrieve hashed password from the database
            $sql = "SELECT password FROM users WHERE email = ?";
            $stmt = $dbconn->prepare($sql);
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->bind_result($hashed_password);

            // Fetch the result and verify password
            if ($stmt->fetch()) {
                if (password_verify($password, $hashed_password)) {
                    $cookie_timeout = $remember_me == 1 ? 30 * 24 * 60 * 60 : 1 * 60 * 60;
                    $dbconn->close();

                    // Set the login cookie
                    setcookie("login_email", $email, time() + $cookie_timeout, "/");

                    // Redirect based on control panel allow-list
                    if (in_array(strtolower($email), $controlPanelAllowedEmails, true)) {
                        header("Location: /control-panel");
                    } else {
                        header("Location: /dashboard");
                    }
                    exit;
                } else {
                    // If password verification fails
                    $_GET['alert_time'] = time();
                    $_GET['error'] = 'yes';
                    $dbconn->close();
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
