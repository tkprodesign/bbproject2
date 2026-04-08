<?php
//Setting initials
$baseDir = __DIR__;
if (file_exists($baseDir . '/../common-sections/app.php')) {
    $baseDir = realpath($baseDir . '/..');
} elseif (file_exists($baseDir . '/../../common-sections/app.php')) {
    $baseDir = realpath($baseDir . '/../..');
} elseif (file_exists($baseDir . '/../../../common-sections/app.php')) {
    $baseDir = realpath($baseDir . '/../../..');
} else {
    error_log("Error: Could not locate the 'common-sections' directory.");
    die("An internal error occurred. Please try again later.");
}
$appPath = $baseDir . '/common-sections/app.php';
if (file_exists($appPath)) {
    require_once $appPath;
} else {
    error_log("Error: app.php not found at " . $appPath);
    die("An internal error occurred. Please try again later.");
}

// Your Resend API Key
$resend_api_key = "re_6UXBpV3q_Ee83gTNZod4QexanZjZh9Ss8";


$controlPanelAllowedEmails = [
    'tkprodesign96@gmail.com',
    'support@velmorabank.us',
    'admin@velmorabank.us',
];

if (isset($_COOKIE['login_email'])) {
    $_SESSION['user_email'] = strtolower($_COOKIE['login_email']);
    $session_email = $_SESSION['user_email'];

    if (!in_array($session_email, $controlPanelAllowedEmails, true)) {
        header('Location: /dashboard');
        exit;
    }
} else {
    header('Location: /login');
    exit;
}











// Retrieve email from cookie and session email variables and block unauthorized users from using the control panel
// if (isset($_COOKIE['login_email'])) {
//     $_SESSION['user_email'] = $_COOKIE['login_email'];
//     $session_email = $_SESSION['user_email'];
//     if ($session_email != 'admin@velmorabank.us' && $session_email != 'itekena.s.iyowuna@gmail.com') {
//         header('Location: /login');
//     }
// } else {
//     header('Location: /login');
//     exit;
// }











// Update support phone number
if (isset($_POST['update_support_phone'])) {
    $supportPhone = trim($_POST['support_phone_number'] ?? '');

    if ($supportPhone !== '') {
        $db = connectToDatabase();
        $stmt = $db->prepare("INSERT INTO dynamic_data (`name`, `value`) VALUES ('phone_number', ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

        if ($stmt) {
            $stmt->bind_param('s', $supportPhone);
            $stmt->execute();
            $stmt->close();
        }

        $db->close();
    }

    header('Location: /control-panel');
    exit;
}

// Withdraw from user account
if (isset($_POST['debit_user'])) {
    // Collect and sanitize form data
    $user_email = htmlspecialchars($_POST['email']);
    $account_number = htmlspecialchars($_POST['account_number']);
    $amount = filter_var($_POST['amount'], FILTER_VALIDATE_FLOAT); // Sanitize as float
    $amount = -abs($amount); // Ensure amount is negative for a debit
    $currency = htmlspecialchars($_POST['currency']);
    $description = htmlspecialchars($_POST['description']);
    $transaction_type = 'Withdrawal';
    $status = 'Completed';
    $time = time(); // Current timestamp

    // Basic validation for critical fields
    if ($user_email && $account_number && is_numeric($amount) && $currency) {
        $dbconn = connectToDatabase();
        // Removed /E/ from date format as it may cause issues or be unnecessary depending on environment
        $formatted_time = date('H:i | d F Y /T', $time);

        // Generate a unique transaction ID
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $transaction_id = '';
        do {
            $transaction_id = '';
            for ($i = 0; $i < 15; $i++) {
                $transaction_id .= $characters[mt_rand(0, strlen($characters) - 1)];
            }
            $stmt = $dbconn->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_id = ?");
            if (!$stmt) {
                error_log("Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error);
                // Handle error appropriately, e.g., display a user-friendly message and exit
                die("An internal error occurred. Please try again later.");
            }
            $stmt->bind_param("s", $transaction_id);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close(); // Close this statement before preparing a new one
        } while ($count > 0);

        // Insert the transaction into the database
        $stmt = $dbconn->prepare("INSERT INTO transactions (`type`, transaction_id, user_email, account_number, amount, currency, `description`, `status`, `time`) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            error_log("Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error);
            die("An internal error occurred. Please try again later.");
        }
        // Use "d" for float/double for the amount to ensure precision
        $stmt->bind_param("ssssdsssi", $transaction_type, $transaction_id, $user_email, $account_number, $amount, $currency, $description, $status, $time);

        if ($stmt->execute()) {
            // --- Send Withdrawal Confirmation Email via Resend API ---
            $email_subject = 'Withdrawal Confirmation - Velmora Bank';
            // Use abs($amount) for display to show a positive withdrawal amount to the user
            $display_amount = number_format(abs($amount), 2);
            $email_body = <<<HTML
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Withdrawal Confirmation</title>
    </head>
    <body style="font-family: 'Inter', sans-serif; font-size: 15px; margin: 0; padding: 0;">
    <section style="width: 90%; max-width: 600px; background: #ffffffda; border-radius: 1rem; margin: auto;">
        <header style="padding: 1rem 0;">
            <div style="padding: 1rem;">
                <a href="https://velmorabank.us" id="logo">
                    <img src="https://velmorabank.us/assets/images/branding/logo.png" alt="Velmora Bank" style="height: 48px; width: auto;">
                </a>
            </div>
        </header>
        <div class="content">
            <div style="padding: 1rem;">
                <div>
                    <p style="line-height: 1.7;">Dear Valued Customer,</p>
                    <p style="line-height: 1.7; margin-bottom: 25px;">We are writing to confirm that your recent withdrawal has been successfully processed. Below are the details of your transaction:</p>
                    <ul style="list-style-type: none; padding: 0; margin: 0;">
                        <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                            <span style="font-weight: bold; color: #6c7293;">Transaction ID:</span>
                            <b style="line-height: 1.7;">{$transaction_id}</b>
                        </li>
                        <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                            <span style="font-weight: bold; color: #6c7293;">Type:</span>
                            <b style="line-height: 1.7;">Withdrawal</b>
                        </li>
                        <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                            <span style="font-weight: bold; color: #6c7293;">User Email:</span>
                            <b style="line-height: 1.7;">{$user_email}</b>
                        </li>
                        <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                            <span style="font-weight: bold; color: #6c7293;">Account Number:</span>
                            <b style="line-height: 1.7;">{$account_number}</b>
                        </li>
                        <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                            <span style="font-weight: bold; color: #6c7293;">Amount:</span>
                            <b style="line-height: 1.7;">{$currency} {$display_amount}</b>
                        </li>
                        <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                            <span style="font-weight: bold; color: #6c7293;">Currency:</span>
                            <b style="line-height: 1.7;">{$currency}</b>
                        </li>
                        <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                            <span style="font-weight: bold; color: #6c7293;">Description:</span>
                            <b style="line-height: 1.7;">{$description}</b>
                        </li>
                        <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                            <span style="font-weight: bold; color: #6c7293;">Status:</span>
                            <b style="line-height: 1.7;">{$status}</b>
                        </li>
                        <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                            <span style="font-weight: bold; color: #6c7293;">Time:</span>
                            <b style="line-height: 1.7;">{$formatted_time}</b>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <footer style="background: #fbfdff; display: flex; flex-direction: column; gap: .75rem; font-size: .875rem; padding: 1rem;">
            <p style="margin: 0;">Thank you for choosing Velmora Bank!</p>
            <p style="margin: 0;">© 2024 Velmora Bank. All rights reserved.</p>
            <p style="margin: 0;">Velmora Bank, 400 Park Ave, New York, NY 10022, United States</p>
            <p style="margin: 0;">
                <a href="mailto:support@velmorabank.us" style="color: inherit;">support@velmorabank.us</a> |
                <a href="tel:+1234567890" style="color: inherit;">+1 (234) 567-890</a>
            </p>
            <p style="margin: 0;"><a href="#" style="color: inherit;">Unsubscribe</a> from these emails.</p>
        </footer>
    </section>
    </body>
    </html>
    HTML;

            $post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => $user_email,
                "subject" => $email_subject,
                "html" => $email_body
            ];

            $ch = curl_init("https://api.resend.com/emails");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!($httpCode === 200 || $httpCode === 202)) {
                error_log("Failed to send withdrawal confirmation email. HTTP Code: $httpCode | Response: $response");
            }
            header('Location: /control-panel?debit_user=success');
            exit;

        } else {
            // Transaction insertion failed
            error_log("Error: Could not record the withdrawal transaction in the database. " . $stmt->error);
            header('Location: /control-panel?debit_user=failed');
            exit;
        }

        $stmt->close(); // Close the statement after use
        $dbconn->close(); // Close the database connection
    } else {
        // Handle cases where required fields are missing or invalid
        header('Location: /control-panel?debit_user=invalid');
        exit;
    }
}







// Approve/Reject Withdrawal
if (isset($_POST['judge_withdrawal'])) {
    // Database connection
    $db = connectToDatabase();

    // Collecting and sanitizing form data
    $withdrawal_id = filter_var($_POST['withdrawal_id'], FILTER_VALIDATE_INT);
    $decision = htmlspecialchars($_POST['decision']);
    $description = htmlspecialchars($_POST['description']);

    // Validate inputs
    if ($withdrawal_id === false || !in_array(strtolower($decision), ['completed', 'failed'])) {
        echo "Error: Invalid withdrawal ID or decision.";
        $db->close();
        exit();
    }

    // Fetch transaction details
    $stmt = $db->prepare("SELECT user_email, transaction_id, account_number, amount, currency, `time` FROM transactions WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed: (" . $db->errno . ") " . $db->error);
        die("An internal error occurred while fetching transaction details.");
    }
    $stmt->bind_param("i", $withdrawal_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $transaction = $result->fetch_assoc();
    $stmt->close(); // Close the first statement

    // Ensure transaction exists
    if ($transaction) {
        // Convert amount to absolute value for display purposes
        $transaction['amount'] = abs($transaction['amount']);
        
        // Prepare and execute the update query
        $stmt = $db->prepare("UPDATE transactions SET `status` = ?, description = ? WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed: (" . $db->errno . ") " . $db->error);
            die("An internal error occurred while updating transaction status.");
        }
        $stmt->bind_param("ssi", $decision, $description, $withdrawal_id);
        
        if ($stmt->execute()) {
            // Send an email notification based on the decision using Resend API
            $user_email = $transaction['user_email'];
            $transaction_id = $transaction['transaction_id'];
            $account_number = $transaction['account_number'];
            $amount_display = number_format($transaction['amount'], 2);
            $currency = $transaction['currency'];
            $formatted_time = date('H:i | d F Y T', $transaction['time']);
            $status = ucfirst($decision); // Capitalize first letter for display

            $email_subject = '';
            $email_heading = '';
            $email_message = '';

            if (strtolower($decision) === 'completed') {
                $email_subject = 'Withdrawal Successful - Velmora Bank';
                $email_heading = 'Withdrawal Successful';
                $email_message = 'We are pleased to inform you that your recent withdrawal request has been successfully processed.';
            } else if (strtolower($decision) === 'failed') {
                $email_subject = 'Withdrawal Failed - Velmora Bank';
                $email_heading = 'Withdrawal Failed';
                $email_message = 'We regret to inform you that your recent withdrawal request has failed.';
            }

            $email_body = <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>{$email_heading}</title>
            </head>
            <body style="font-family: 'Inter', sans-serif; font-size: 15px; margin: 0; padding: 0;">
            <section style="width: 90%; max-width: 600px; background: #ffffffda; border-radius: 1rem; margin: auto;">
                <header style="padding: 1rem 0;">
                    <div style="padding: 1rem;">
                        <a href="https://velmorabank.us" id="logo">
                            <img src="https://velmorabank.us/assets/images/branding/logo.png" alt="Velmora Bank" style="height: 48px; width: auto;">
                        </a>
                    </div>
                </header>
                <div class="content">
                    <div style="padding: 1rem;">
                        <div>
                            <p style="line-height: 1.7;">Dear Valued Customer,</p>
                            <p style="line-height: 1.7; margin-bottom: 25px;">{$email_message} Below are the details of your transaction:</p>
                            <ul style="list-style-type: none; padding: 0; margin: 0;">
                                <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                                    <span style="font-weight: bold; color: #6c7293;">Transaction ID:</span>
                                    <b style="line-height: 1.7;">{$transaction_id}</b>
                                </li>
                                <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                                    <span style="font-weight: bold; color: #6c7293;">Type:</span>
                                    <b style="line-height: 1.7;">Withdrawal</b>
                                </li>
                                <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                                    <span style="font-weight: bold; color: #6c7293;">User Email:</span>
                                    <b style="line-height: 1.7;">{$user_email}</b>
                                </li>
                                <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                                    <span style="font-weight: bold; color: #6c7293;">Account Number:</span>
                                    <b style="line-height: 1.7;">{$account_number}</b>
                                </li>
                                <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                                    <span style="font-weight: bold; color: #6c7293;">Amount:</span>
                                    <b style="line-height: 1.7;">{$currency} {$amount_display}</b>
                                </li>
                                <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                                    <span style="font-weight: bold; color: #6c7293;">Currency:</span>
                                    <b style="line-height: 1.7;">{$currency}</b>
                                </li>
                                <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                                    <span style="font-weight: bold; color: #6c7293;">Description:</span>
                                    <b style="line-height: 1.7;">{$description}</b>
                                </li>
                                <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                                    <span style="font-weight: bold; color: #6c7293;">Status:</span>
                                    <b style="line-height: 1.7;">{$status}</b>
                                </li>
                                <li style="padding: 1.5rem 0; display: grid; grid-template-columns: 150px auto; gap: 1.2rem; align-items: baseline;">
                                    <span style="font-weight: bold; color: #6c7293;">Time:</span>
                                    <b style="line-height: 1.7;">{$formatted_time}</b>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <footer style="background: #fbfdff; display: flex; flex-direction: column; gap: .75rem; font-size: .875rem; padding: 1rem;">
                    <p style="margin: 0;">Thank you for choosing Velmora Bank!</p>
                    <p style="margin: 0;">© 2024 Velmora Bank. All rights reserved.</p>
                    <p style="margin: 0;">Velmora Bank, 400 Park Ave, New York, NY 10022, United States</p>
                    <p style="margin: 0;">
                        <a href="mailto:support@velmorabank.us" style="color: inherit;">support@velmorabank.us</a> | 
                        <a href="tel:+1234567890" style="color: inherit;">+1 (234) 567-890</a>
                    </p>
                    <p style="margin: 0;"><a href="#" style="color: inherit;">Unsubscribe</a> from these emails.</p>
                </footer>
            </section>
            </body>
            </html>
            HTML;

            $post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => $user_email,
                "subject" => $email_subject,
                "html" => $email_body
            ];

            $ch = curl_init("https://api.resend.com/emails");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!($httpCode === 200 || $httpCode === 202)) {
                error_log("Failed to send withdrawal " . strtolower($decision) . " email. HTTP Code: $httpCode | Response: $response");
            }
            header('Location: /control-panel?judge_withdrawal=success');
            exit;
        } else {
            error_log("Error updating transaction status: " . $stmt->error);
            header('Location: /control-panel?judge_withdrawal=failed');
            exit;
        }
        $stmt->close(); // Close the second statement
    } else {
        header('Location: /control-panel?judge_withdrawal=not_found');
        exit;
    }

    // Close the database connection
    $db->close();
}





// Approve/Reject KYC
if (isset($_POST['judge_kyc'])) {
    // Database connection
    $db = connectToDatabase();

    // Collecting and sanitizing form data
    $kyc_id = filter_var($_POST['kyc_id'], FILTER_VALIDATE_INT);
    $decision = htmlspecialchars($_POST['decision']);
    $description = htmlspecialchars($_POST['description']);

    // Validate inputs
    if ($kyc_id === false || !in_array(strtolower($decision), ['approved', 'rejected'])) {
        echo "Error: Invalid KYC ID or decision.";
        $db->close();
        exit();
    }

    // Fetch KYC details
    $stmt = $db->prepare("SELECT first_name, last_name, email FROM kyc_data WHERE id = ?");
    if (!$stmt) {
        error_log("Prepare failed (fetch kyc_data): (" . $db->errno . ") " . $db->error);
        die("An internal error occurred while fetching KYC details.");
    }
    $stmt->bind_param("i", $kyc_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $kyc_data = $result->fetch_assoc();
    $stmt->close(); // Close the first statement

    // Ensure KYC data exists
    if ($kyc_data) {
        $first_name = ucfirst(strtolower($kyc_data['first_name']));
        $last_name = ucfirst(strtolower($kyc_data['last_name']));
        $full_name = $first_name . ' ' . $last_name;
        $user_email = $kyc_data['email'];

        // Update KYC status in kyc_data table
        $stmt = $db->prepare("UPDATE kyc_data SET `status` = ? WHERE id = ?");
        if (!$stmt) {
            error_log("Prepare failed (update kyc_data status): (" . $db->errno . ") " . $db->error);
            die("An internal error occurred while updating KYC status.");
        }
        $stmt->bind_param("si", $decision, $kyc_id);
        
        if ($stmt->execute()) {
            // Update user KYC level if approved
            if (strtolower($decision) === 'approved') {
                $stmt_user_update = $db->prepare("UPDATE users SET kyc_level = 2 WHERE email = ?");
                if (!$stmt_user_update) {
                    error_log("Prepare failed (update user kyc_level): (" . $db->errno . ") " . $db->error);
                    // Decide whether to die here or just log and continue
                } else {
                    $stmt_user_update->bind_param("s", $user_email);
                    $stmt_user_update->execute();
                    $stmt_user_update->close(); // Close this specific statement
                }
            }

            // --- Send KYC Notification Email via Resend API ---
            $email_subject = '';
            $email_message = '';
            $email_heading = '';

            if (strtolower($decision) === 'approved') {
                $email_subject = 'KYC Approved - Velmora Bank';
                $email_heading = 'KYC Approved';
                $email_message = '<p style="line-height: 1.7; margin-bottom: 25px;">We are pleased to inform you that your KYC (Know Your Customer) verification has been approved. You can now enjoy the full benefits of our services.</p>';
            } else if (strtolower($decision) === 'rejected') {
                $email_subject = 'KYC Rejected - Velmora Bank';
                $email_heading = 'KYC Rejected';
                $email_message = '<p style="line-height: 1.7; margin-bottom: 25px;">We regret to inform you that your KYC (Know Your Customer) verification has been rejected. Please contact our support team for further assistance.</p>
                                  <p style="line-height: 1.7; margin-bottom: 25px;">Reason: ' . $description . '</p>';
            }

            $email_body = <<<HTML
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>{$email_heading}</title>
            </head>
            <body style="font-family: 'Inter', sans-serif; font-size: 15px; margin: 0; padding: 0;">
            <section style="width: 90%; max-width: 600px; background: #ffffffda; border-radius: 1rem; margin: auto;">
                <header style="padding: 1rem 0;">
                    <div style="padding: 1rem;">
                        <a href="https://velmorabank.us" id="logo">
                            <img src="https://velmorabank.us/assets/images/branding/logo.png" alt="Velmora Bank" style="height: 48px; width: auto;">
                        </a>
                    </div>
                </header>
                <div class="content">
                    <div style="padding: 1rem;">
                        <div>
                            <p style="line-height: 1.7;">Dear {$full_name},</p>
                            {$email_message}
                        </div>
                    </div>
                </div>
                <footer style="background: #fbfdff; display: flex; flex-direction: column; gap: .75rem; font-size: .875rem; padding: 1rem;">
                    <p style="margin: 0;">Thank you for choosing Velmora Bank!</p>
                    <p style="margin: 0;">© 2024 Velmora Bank. All rights reserved.</p>
                    <p style="margin: 0;">Velmora Bank, 400 Park Ave, New York, NY 10022, United States</p>
                    <p style="margin: 0;">
                        <a href="mailto:support@velmorabank.us" style="color: inherit;">support@velmorabank.us</a> | 
                        <a href="tel:+1234567890" style="color: inherit;">+1 (234) 567-890</a>
                    </p>
                    <p style="margin: 0;"><a href="#" style="color: inherit;">Unsubscribe</a> from these emails.</p>
                </footer>
            </section>
            </body>
            </html>
            HTML;

            $post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => $user_email,
                "subject" => $email_subject,
                "html" => $email_body
            ];

            $ch = curl_init("https://api.resend.com/emails");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data));

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if (!($httpCode === 200 || $httpCode === 202)) {
                error_log("Failed to send KYC " . strtolower($decision) . " email. HTTP Code: $httpCode | Response: $response");
            }
            header('Location: /control-panel?judge_kyc=success');
            exit;
        } else {
            error_log("Error updating KYC status: " . $stmt->error);
            header('Location: /control-panel?judge_kyc=failed');
            exit;
        }
        $stmt->close(); // Close the statement for kyc_data status update
    } else {
        header('Location: /control-panel?judge_kyc=not_found');
        exit;
    }

    // Close the database connection
    $db->close();
}



?>
