<?php
require_once __DIR__ . '/../common-sections/app.php';









// Customer identity is resolved from the server-side authenticated session.
$controlPanelAllowedEmails = [
    'tkprodesign96@gmail.com',
    'support@velmorabank.us',
    'admin@velmorabank.us',
];
$dashboardLoginRoute = defined('VELMORA_LOGIN_ROUTE') ? VELMORA_LOGIN_ROUTE : '/login/';
$dashboardControlPanelRoute = defined('VELMORA_CONTROL_PANEL_ROUTE') ? VELMORA_CONTROL_PANEL_ROUTE : '/control-panel/';

$session_email = velmoraCurrentCustomerEmail();
if ($session_email === null) {
    header('Location: ' . $dashboardLoginRoute);
    exit;
}
$_SESSION['user_email'] = $session_email;

if (in_array($session_email, $controlPanelAllowedEmails, true)) {
    header('Location: ' . $dashboardControlPanelRoute);
    exit;
}

normalizeLegacyTransactionStatuses();





// Legacy logout query support.
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    velmoraLogoutCustomer(true);
    header('Location: ' . $dashboardLoginRoute);
    exit;
}


//Get user data from users table
$dbconn = connectToDatabase();
$sql = "SELECT `name`, email, kyc_level, profile_picture, last_active, user_status FROM users WHERE email = ?";
$stmt = $dbconn->prepare($sql);
$hasUser = false;


if ($stmt) {
    $stmt->bind_param('s', $session_email);
    $stmt->execute();
    $stmt->bind_result($user_name, $user_email, $user_kyc_level, $user_profile_picture, $user_last_active, $user_status);
    $hasUser = $stmt->fetch();
    $stmt->close();
}
$dbconn->close();

if (empty($hasUser) || empty($user_email)) {
    velmoraLogoutCustomer(true);
    header('Location: ' . $dashboardLoginRoute);
    exit;
}

if (!in_array(strtolower(trim((string)$user_status)), ['active','enabled'], true)) {
    velmoraLogoutCustomer(true);
    header('Location: ' . $dashboardLoginRoute . '?restricted=yes');
    exit;
}


// Production dashboard contains no customer-specific seed behavior.




//Get user's number of accounts from accounts table
$dbconn = connectToDatabase();
$sql = "SELECT COUNT(*) FROM accounts WHERE user_email = ?";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($accounts_count);
$stmt->fetch();
$stmt->close();
$dbconn->close();





//Sum up user's balance from transaction table
$dbconn = connectToDatabase();
$sql = "SELECT SUM(amount) AS user_balance FROM transactions WHERE user_email = ? AND (status IS NULL OR LOWER(status) <> 'failed')";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($user_balance);
$stmt->fetch();
$stmt->close();
$dbconn->close();
$user_balance = $user_balance > 0 ? number_format($user_balance, 2) : '0.00';






//Count how many transactions have been made
$dbconn = connectToDatabase();
$sql = "SELECT COUNT(*) FROM transactions WHERE user_email = ? AND (status IS NULL OR LOWER(status) <> 'failed')";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($transaction_count);
$stmt->fetch();
$stmt->close();
$dbconn->close();




//1. accounts/create

function renderBankEmailTemplate($subject, $headline, $introHtml, $detailsHtml, $ctaText = '', $ctaUrl = '') {
    $logoUrl = 'https://velmorabank.us/assets/images/branding/logo.png';
    $ctaBlock = '';
    if (!empty($ctaText) && !empty($ctaUrl)) {
        $ctaBlock = '<tr><td align="center" style="padding: 0 32px 24px 32px;"><a href="' . htmlspecialchars($ctaUrl, ENT_QUOTES, 'UTF-8') . '" style="display:inline-block;background:#0ddbb9;color:#0f1f33;text-decoration:none;font-weight:700;font-size:14px;line-height:1;padding:14px 22px;border-radius:6px;">' . htmlspecialchars($ctaText, ENT_QUOTES, 'UTF-8') . '</a></td></tr>';
    }

    return '<!DOCTYPE html><html lang="en"><head>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</title></head><body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#1a2b44;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6fb;padding:24px 0;"><tr><td align="center"><table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px;max-width:94%;background:#ffffff;border:1px solid #e4e9f2;border-radius:12px;overflow:hidden;"><tr><td style="background:#0f2742;padding:22px 28px;"><img src="' . $logoUrl . '" alt="Velmora Bank" style="height:36px;width:auto;display:block;"></td></tr><tr><td style="padding:28px 32px 8px 32px;"><p style="margin:0 0 8px 0;font-size:12px;letter-spacing:.08em;color:#6f8199;text-transform:uppercase;">Velmora Bank Notification</p><h1 style="margin:0;font-size:24px;line-height:1.35;color:#0f2742;">' . htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') . '</h1></td></tr><tr><td style="padding:0 32px 10px 32px;font-size:15px;line-height:1.7;color:#3a4a62;">' . $introHtml . '</td></tr><tr><td style="padding:6px 32px 24px 32px;">' . $detailsHtml . '</td></tr>' . $ctaBlock . '<tr><td style="padding:18px 32px;background:#f8faff;border-top:1px solid #e4e9f2;"><p style="margin:0 0 6px 0;font-size:12px;line-height:1.5;color:#6f8199;">Velmora Bank · Secure online banking</p><p style="margin:0;font-size:12px;line-height:1.5;color:#6f8199;">Need help? <a href="mailto:support@velmorabank.us" style="color:#0f2742;text-decoration:none;font-weight:600;">support@velmorabank.us</a></p></td></tr></table></td></tr></table></body></html>';
}


//Create account button function
if (isset($_POST['create_account'])) {
    $user_name = $_POST['user_name'];
    $user_email = $user_email; 
    $currency = strtoupper(trim((string)($_POST['currency'] ?? 'USD')));
    if (!velmoraIsSupportedCurrency($currency)) {
        $currency = 'USD';
    }
    $account_type = $_POST['account_type'];
    $time = time();


    $dbconn = connectToDatabase();
    do {
        $randomNumber = random_int(100000000, 999999999);
        $bank_account_number_str = '2' . $randomNumber;
        $bank_account_number = (int)$bank_account_number_str;
        $sql = "SELECT COUNT(*) FROM accounts WHERE account_number = $bank_account_number";
        $result = $dbconn->query($sql);
        if ($result === false) {
            die("Error executing query: " . $dbconn->error);
        }
        $row = $result->fetch_row();
        $count = $row[0];
        $result->free();
    } while ($count > 0);

   

    // Define the email subject from your PHPMailer example
    $email_subject = 'Your New Velmora Bank Account Has Been Successfully Created';

    $introHtml = '<p style="margin:0;">Your new account has been opened successfully and is ready for use.</p>';
    $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;"><tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Account Number</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars((string)$bank_account_number, ENT_QUOTES, 'UTF-8') . '</td></tr><tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status</td><td style="padding:12px 16px;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">Active</td></tr></table>';
    $email_body = renderBankEmailTemplate($email_subject, 'Account Successfully Created', $introHtml, $detailsHtml, 'View Account', 'https://velmorabank.us/dashboard/accounts');


    if (!sendSiteEmail($user_email, $email_subject, $email_body)) {
        error_log('Failed to send account creation email via SMTP.');
    }

    // // Create a new PHPMailer instance
    // $mail = new PHPMailer(true);

    // try {
    //     //Server settings
    //     $mail->isSMTP();                                            // Set mailer to use SMTP
    //     $mail->Host       = 'velmorabank.us';                     // Specify main and backup SMTP servers
    //     $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    //     $mail->Username   = 'no-reply@velmorabank.us';               // SMTP username
    //     $mail->Password   = getenv('NOREPLY_EMAIL_PASSWORD') ?: '';                  // SMTP password
    //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption, `ssl` also accepted
    //     $mail->Port       = 587;                                    // TCP port to connect to

    //     //Recipients
    //     $mail->setFrom('no-reply@velmorabank.us', 'Velmora Bank Notifications');
    //     $mail->addAddress($user_email);                 // Add a recipient

    //     // Content
    //     $mail->isHTML(true);                                        // Set email format to HTML
    //     $mail->Subject = 'Your New Velmora Bank Account Has Been Successfully Created';                                        // Empty subject
    //     $mail->Body    = 
    //                 '<!DOCTYPE html>
    //                 <html lang="en">
    //                 <head>
    //                     <meta charset="UTF-8">
    //                     <meta name="viewport" content="width=device-width, initial-scale=1.0">
    //                     <title>Your New Velmora Bank Account Has Been Successfully Created</title>
    //                 </head>
    //                 <body style="font-family: Inter, sans-serif; padding: 0; margin: 0; background: #fff;">
    //                     <section style="width: 90%; max-width: 600px; border-radius: 1rem; margin: auto;">
    //                         <header style="padding: 1rem 0;">
    //                             <div style="padding: 1rem;">
    //                                 <a href="https://velmorabank.us" id="logo">
    //                                     <img src="https://velmorabank.us/assets/images/branding/logo.png" alt="Velmora Bank" style="height: 48px; width: auto;">
    //                                 </a>
    //                             </div>
    //                         </header>
    //                         <div class="content">
    //                             <div style="padding: 1rem;">
    //                                 <div class="account-success">
    //                                     <div class="wrapper" style="width: 90%; max-width: 500px; margin: 60px auto; display: flex; flex-direction: column; align-items: center; padding: 1.875rem 1.875rem; border-radius: 6px; border: 1px solid #e7eaed; background-color: #fff; color: #6c7293;">
    //                                         <div style="font-size: 58px; line-height: 1; margin-bottom: 20px;" aria-hidden="true">✓</div>
    //                                         <p style="text-align: center; margin-bottom: 25px;">Congratulations, your new account has been created with account number <strong>'.$bank_account_number.'</strong>.</p>
    //                                         <a href="https://velmorabank.us/dashboard/accounts" class="cta" style="padding: 0.625rem 1.125rem; color: #fff; background-color: #0ddbb9; border-color: #0ddbb9; border-radius: 0.25rem; display: inline-block; box-shadow: 0 2px 2px 0 rgba(13, 219, 185, 0.14), 0 3px 1px -2px rgba(13, 219, 185, 0.2), 0 1px 5px 0 rgba(13, 219, 185, 0.12); text-decoration: none; font-weight: 600;">View Details</a>
    //                                     </div>
    //                                 </div>
    //                             </div>
    //                         </div>
    //                         <footer style="background: #fbfdff; display: flex; flex-direction: column; gap: .75rem; font-size: .875rem; padding: 1rem;">
    //                             <p style="margin: 0;">Thank you for choosing Velmora Bank!</p>
    //                             <p style="margin: 0;">© 2024 Velmora Bank. All rights reserved.</p>
    //                             <p style="margin: 0;">400 Park Ave, New York, NY 10022, United States</p>
    //                             <p style="margin: 0;">
    //                                 <a href="mailto:support@velmorabank.us" style="color: inherit;">support@velmorabank.us</a> | 
    //                                 <a href="tel:+1234567890" style="color: inherit;">+1 (234) 567-890</a>
    //                             </p>
    //                             <!-- Uncomment if needed
    //                             <div class="social-media-links" style="margin: 10px 0;">
    //                                 <a href="https://facebook.com/#" style="margin: 0 5px;">
    //                                     <img src="/assets/images/social/facebook.png" alt="Facebook" style="width: 24px; height: 24px;">
    //                                 </a>
    //                                 <a href="https://twitter.com/#" style="margin: 0 5px;">
    //                                     <img src="/assets/images/social/twitter.png" alt="Twitter" style="width: 24px; height: 24px;">
    //                                 </a>
    //                                 <a href="https://linkedin.com/#" style="margin: 0 5px;">
    //                                     <img src="/assets/images/social/linkedin.png" alt="LinkedIn" style="width: 24px; height: 24px;">
    //                                 </a>
    //                             </div>
    //                             -->
    //                             <p style="margin: 0;"><a href="#" style="color: inherit;">Unsubscribe</a> from these emails.</p>
    //                         </footer>
    //                     </section>
    //                 </body>
    //                 </html>';                                       

    //     $mail->send();


    //     $message_sent = 'yes';
    // } catch (Exception $e) {
    //     echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    // }

    


    $sql = "INSERT INTO accounts (account_type, user_name, user_email, currency, account_number, creation_time) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $dbconn->prepare($sql);
    $stmt->bind_param('sssssi', $account_type, $user_name, $user_email, $currency, $bank_account_number, $time);
    if ($stmt->execute()) {
        $stmt->close();
        header('Location: ../success?nos='.$bank_account_number.'s');
        exit();
    } else {
        echo "Error executing statement: " . $dbconn->error . "<br>";
    }
    $dbconn->close();
}





//2 security/complete-kyc
function handleProfilePictureUpload($dbconn, $user_email) {
    if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
        return 'Please select a valid image file.';
    }

    $file = $_FILES['profile_picture'];
    $originalName = basename($file["name"]);
    $fileExtension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $targetDir = __DIR__ . "/security/complete-kyc/uploads/";

    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true)) {
        return 'Upload directory is not available.';
    }
    if (!is_writable($targetDir)) {
        return 'Upload directory is not writable.';
    }

    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return 'File is not an image.';
    }
    if ($file["size"] > 2 * 1024 * 1024) {
        return 'File is too large.';
    }
    if (!in_array($fileExtension, ['jpg', 'jpeg', 'png'])) {
        return 'Only JPG, JPEG & PNG files are allowed.';
    }
    $fileName = uniqid('profile_', true) . '.' . $fileExtension;
    $targetFile = $targetDir . $fileName;

    if (!move_uploaded_file($file["tmp_name"], $targetFile)) {
        return 'Sorry, there was an error uploading your file.';
    }

    $stmt = $dbconn->prepare("UPDATE users SET profile_picture = ? WHERE email = ?");
    if (!$stmt) {
        return "Prepare failed: " . $dbconn->error;
    }
    $stmt->bind_param("ss", $fileName, $user_email);
    $ok = $stmt->execute();
    $stmt->close();

    return $ok ? 'Profile picture updated successfully!' : 'Failed to update profile picture.';
}

if (isset($_POST['submit_profile_picture'])) {
    $dbconn = connectToDatabase();
    $ppstate = handleProfilePictureUpload($dbconn, $user_email);
    $dbconn->close();
}

//Submit KYC data
if (isset($_POST['submit_kyc_data'])) {
    $dbconn = connectToDatabase();
    if ($dbconn->connect_error) {
        die("Connection failed: " . $dbconn->connect_error);
    }

    // if ($file['error'] === UPLOAD_ERR_OK) {
    //     $uploadDir = '../uploads/'; // make sure this directory exists and is writable
    //     $fileName = basename($file['name']);
    //     $targetPath = $uploadDir . $fileName;

    //     if (move_uploaded_file($file['tmp_name'], $targetPath)) {
    //         // Update the user's profile_picture column
    //         $stmt = $dbconn->prepare("UPDATE users SET profile_picture = ? WHERE email = ?");
    //         if ($stmt) {
    //             $stmt->bind_param("ss", $fileName, $email);
    //             if ($stmt->execute()) {
    //                 echo "Profile picture updated successfully!";
    //             } else {
    //                 echo "Execute failed: " . $stmt->error;
    //             }
    //             $stmt->close();
    //         } else {
    //             echo "Prepare failed: " . $dbconn->error;
    //         }
    //     } else {
    //         echo "Failed to move uploaded file.";
    //     }
    // } else {
    //     echo "Upload error: " . $file['error'];
    // }

    $first_name = $_POST['first_name'];
    $middle_name = $_POST['middle_name'];
    $last_name = $_POST['last_name'];
    $suffix = $_POST['suffix'];
    $gender = $_POST['gender'];
    $address1 = $_POST['address1'];
    $address2 = $_POST['address2'];
    $apartment_no = $_POST['apartment_no'];
    $city = $_POST['city'];
    $state = $_POST['state'];
    $phone_number = $_POST['phone_number'];
    $date_of_birth = $_POST['date_of_birth'];
    $zip_code = $_POST['zip_code'];
    $us_citizen = $_POST['us_citizen'];
    $dual_citizenship = $_POST['dual_citizenship'];
    $country_of_residence = $_POST['country_of_residence'];
    $source_of_income = $_POST['source_of_income'];
    $nationality = $_POST['nationality'];
    $email = $user_email;  // Assuming $user_email is already defined
    $time_uploaded = date('Y-m-d H:i:s'); // Current timestamp

    $sql = "INSERT INTO kyc_data  (
                first_name, middle_name, last_name, suffix, gender, address1, address2, apartment_no, city, state,
                phone_number, date_of_birth, zip_code, us_citizen, dual_citizenship, country_of_residence,
                source_of_income, nationality, email, time_uploaded
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $dbconn->prepare($sql);
    if ($stmt === false) {
        die("Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error);
    }

    $stmt->bind_param(
        'ssssssssssssssssssss',
        $first_name, $middle_name, $last_name, $suffix, $gender, $address1, $address2, $apartment_no, $city, $state,
        $phone_number, $date_of_birth, $zip_code, $us_citizen, $dual_citizenship, $country_of_residence,
        $source_of_income, $nationality, $email, $time_uploaded
    );

    if ($stmt->execute()) {
        $sql = "UPDATE users SET kyc_level = 2 WHERE email = ?";
        $stmt = $dbconn->prepare($sql);
        if ($stmt === false) {
            die("Prepare failed: (" . $dbconn->errno . ") " . $dbconn->error);
        }

        $stmt->bind_param('s', $user_email);

        if ($stmt->execute()) {
            $Update = 'successful';
        } else {
            echo "Error executing query: (" . $stmt->errno . ") " . $stmt->error . "<br>";
        }

        // $stmt->close();

        header('Location: /dashboard');
        exit();
    } else {
        echo "Error executing query: (" . $stmt->errno . ") " . $stmt->error . "<br>";
    }

    $stmt->close();
    $dbconn->close();
}





//2 funds/transfer
if (isset($_POST['transfer_funds'])) {
    $to_bank_name = trim((string)($_POST['bank_name'] ?? ''));
    $to_account_number = trim((string)($_POST['account_number'] ?? ''));
    $to_account_type = trim((string)($_POST['account_type'] ?? ''));
    $recipient_currency = strtoupper(trim((string)($_POST['currency'] ?? '')));
    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $from_account_number = (int) preg_replace('/\D+/', '', (string)($_POST['from_account'] ?? ''));
    $time = time();

    if ($to_bank_name === '' || $to_account_number === '' || $from_account_number <= 0
        || $amount === false || $amount <= 0 || !velmoraIsSupportedCurrency($recipient_currency)) {
        header('Location: /dashboard/fund/transfer?transfer=invalid');
        exit();
    }

    $db = connectToDatabase();

    $accountStmt = $db->prepare("SELECT account_type, currency FROM accounts WHERE user_email = ? AND account_number = ? AND account_status = 'Active' LIMIT 1");
    if (!$accountStmt) {
        $db->close();
        header('Location: /dashboard/fund/transfer?transfer=failed');
        exit();
    }
    $accountStmt->bind_param('si', $user_email, $from_account_number);
    $accountStmt->execute();
    $accountStmt->bind_result($from_account_type, $source_currency);
    $accountFound = $accountStmt->fetch();
    $accountStmt->close();

    if (!$accountFound || !velmoraIsSupportedCurrency((string)$source_currency)) {
        $db->close();
        header('Location: /dashboard/fund/transfer?transfer=account');
        exit();
    }

    $source_currency = strtoupper((string)$source_currency);

    $balanceStmt = $db->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE account_number = ? AND (status IS NULL OR LOWER(status) <> 'failed')");
    $balanceStmt->bind_param('i', $from_account_number);
    $balanceStmt->execute();
    $balanceStmt->bind_result($available_balance);
    $balanceStmt->fetch();
    $balanceStmt->close();
    $available_balance = (float)$available_balance;

    if ((float)$amount > $available_balance) {
        $db->close();
        header('Location: /dashboard/fund/transfer?transfer=insufficient');
        exit();
    }

    try {
        $fx = velmoraFxQuote((float)$amount, $source_currency, $recipient_currency);
    } catch (Throwable $e) {
        $db->close();
        header('Location: /dashboard/fund/transfer?transfer=fx');
        exit();
    }

    $recipient_amount = (float)$fx['amount_out'];
    $fx_rate = (float)$fx['customer_rate'];
    $fx_spread_bps = (int)$fx['spread_bps'];

    do {
        $transaction_id = bin2hex(random_bytes(8));
        $check = $db->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_id = ?");
        $check->bind_param('s', $transaction_id);
        $check->execute();
        $check->bind_result($existingCount);
        $check->fetch();
        $check->close();
    } while ((int)$existingCount > 0);

    $negative_amount = -abs((float)$amount);
    $status = 'Pending';
    $type = 'Transfer';
    $description = 'Transfer to ' . $to_bank_name . ' account number ' . $to_account_number;
    if ($source_currency !== $recipient_currency) {
        $description .= ' • Recipient amount ' . velmoraFormatCurrency($recipient_amount, $recipient_currency);
    }

    $stmt = $db->prepare("INSERT INTO transactions
        (transaction_id, `type`, user_email, account_number, amount, currency, `description`, `status`, `time`,
         to_bank_name, to_account_type, to_account_number, counter_currency, counter_amount, fx_rate, fx_spread_bps)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        $db->close();
        header('Location: /dashboard/fund/transfer?transfer=failed');
        exit();
    }

    $stmt->bind_param(
        "sssidsssissssddi",
        $transaction_id,
        $type,
        $user_email,
        $from_account_number,
        $negative_amount,
        $source_currency,
        $description,
        $status,
        $time,
        $to_bank_name,
        $to_account_type,
        $to_account_number,
        $recipient_currency,
        $recipient_amount,
        $fx_rate,
        $fx_spread_bps
    );

    if (!$stmt->execute()) {
        error_log('Transfer insert failed: ' . $stmt->error);
        $stmt->close();
        $db->close();
        header('Location: /dashboard/fund/transfer?transfer=failed');
        exit();
    }

    $stmt->close();
    $db->close();

    $sourceDisplay = velmoraFormatCurrency((float)$amount, $source_currency);
    $recipientDisplay = velmoraFormatCurrency($recipient_amount, $recipient_currency);
    $rateDisplay = '1 ' . $source_currency . ' = ' . number_format($fx_rate, 6) . ' ' . $recipient_currency;
    $spreadDisplay = number_format($fx_spread_bps / 100, 2) . '%';

    $admin_email_subject = 'New Transfer Attempt';
    $admin_intro = '<p style="margin:0;">A new outbound transfer has been initiated by a client and is currently pending compliance review.</p>';
    $admin_details = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">From Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars((string)$from_account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Destination Bank</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($to_bank_name, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Destination Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($to_account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Account Debit</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($sourceDisplay, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Recipient Gets</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($recipientDisplay, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Bank FX Rate</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($rateDisplay, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;">FX Margin</td><td style="padding:12px 16px;text-align:right;font-weight:700;">' . htmlspecialchars($spreadDisplay, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '</table>';
    $admin_email_body = renderBankEmailTemplate($admin_email_subject, 'New Transfer Attempt', $admin_intro, $admin_details);
    if (!sendSiteEmail('admin@velmorabank.us', $admin_email_subject, $admin_email_body)) {
        error_log('Failed to send admin transfer notification via SMTP.');
    }

    $user_email_subject = 'New Transfer Initiated';
    $user_intro = '<p style="margin:0;">Dear ' . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') . ', your transfer request has been received and is now awaiting approval.</p>';
    $user_details = $admin_details;
    $user_email_body = renderBankEmailTemplate($user_email_subject, 'Transfer Initiated', $user_intro, $user_details, 'View Transactions', 'https://velmorabank.us/dashboard/accounts/transactions');
    if (!sendSiteEmail($user_email, $user_email_subject, $user_email_body)) {
        error_log('Failed to send user transfer notification via SMTP.');
    }

    header('Location: /dashboard/accounts/transactions?transfer=pending');
    exit();
}

?>
