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
                    $accountAllowed = true;
                    try {
                        $statusStmt = $dbconn->prepare("SELECT user_status FROM users WHERE email = ? LIMIT 1");
                        if ($statusStmt) {
                            $statusStmt->bind_param('s', $email);
                            $statusStmt->execute();
                            $statusStmt->bind_result($userStatus);
                            if ($statusStmt->fetch() && !in_array(strtolower(trim((string)$userStatus)), ['active','enabled'], true)) {
                                $accountAllowed = false;
                            }
                            $statusStmt->close();
                        }
                    } catch (Throwable $e) {
                        error_log('User status check skipped: ' . $e->getMessage());
                    }

                    if (!$accountAllowed) {
                        $_GET['alert_time'] = time();
                        $_GET['error'] = 'yes';
                        $dbconn->close();
                    } else {
                    $cookie_timeout = $remember_me == 1 ? 30 * 24 * 60 * 60 : 1 * 60 * 60;
                    $loginTime = time();
                    $loginAt = date('Y-m-d H:i:s', $loginTime);
                    $loginIp = substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
                    $userAgent = substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 500);

                    try {
                        $trackStmt = $dbconn->prepare("UPDATE users SET last_active=?, last_login_at=?, last_login_ip=?, login_count=login_count+1 WHERE email=?");
                        if ($trackStmt) {
                            $trackStmt->bind_param('isss', $loginTime, $loginAt, $loginIp, $email);
                            $trackStmt->execute();
                            $trackStmt->close();
                        }
                        $eventType = 'Sign In';
                        $eventDescription = 'Successful online banking sign-in';
                        $eventStmt = $dbconn->prepare("INSERT INTO security_events (user_email,event_type,description,ip_address,user_agent) VALUES (?,?,?,?,?)");
                        if ($eventStmt) {
                            $eventStmt->bind_param('sssss', $email, $eventType, $eventDescription, $loginIp, $userAgent);
                            $eventStmt->execute();
                            $eventStmt->close();
                        }
                    } catch (Throwable $e) {
                        error_log('Login activity tracking skipped: ' . $e->getMessage());
                    }
                    $dbconn->close();

                    // Set the login cookie with secure browser protections.
                    if (session_status() === PHP_SESSION_ACTIVE) {
                        session_regenerate_id(true);
                        $_SESSION['login_email'] = $email;
                    }
                    setcookie("login_email", $email, [
                        'expires' => time() + $cookie_timeout,
                        'path' => '/',
                        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                        'httponly' => true,
                        'samesite' => 'Lax',
                    ]);

                    // Customer sign-in always stays inside customer banking.
                    $loginTarget = defined('VELMORA_LOGIN_TARGET') ? VELMORA_LOGIN_TARGET : '/dashboard';
                    header("Location: " . $loginTarget);
                    exit;
                    }
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
