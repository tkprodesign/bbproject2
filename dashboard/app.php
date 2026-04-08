<?php
if (file_exists('../common-sections/app.php')) {
    require '../common-sections/app.php';
} elseif (file_exists('../../common-sections/app.php')) {
    require '../../common-sections/app.php';
} else {
    require '../../../common-sections/app.php';
}






// Require PHP Admin
if (file_exists('../PHPMailer/src/PHPMailer.php')) {
    require '../PHPMailer/src/PHPMailer.php';
    require '../PHPMailer/src/SMTP.php';
    require '../PHPMailer/src/Exception.php';
} elseif (file_exists('../../PHPMailer/src/PHPMailer.php')) {
    require '../../PHPMailer/src/PHPMailer.php';
    require '../../PHPMailer/src/SMTP.php';
    require '../../PHPMailer/src/Exception.php'; 
} else {
    require '../../../PHPMailer/src/PHPMailer.php';
    require '../../../PHPMailer/src/SMTP.php';
    require '../../../PHPMailer/src/Exception.php'; 
}
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;






// Retrieve email from cookie and session email variables
$controlPanelAllowedEmails = [
    'tkprodesign96@gmail.com',
    'support@velmorabank.us',
    'admin@velmorabank.us',
];

if (isset($_COOKIE['login_email'])) {
    $cookieEmail = strtolower(trim((string)$_COOKIE['login_email']));
    if (!filter_var($cookieEmail, FILTER_VALIDATE_EMAIL)) {
        setcookie('login_email', '', time() - 3600, '/');
        session_unset();
        session_destroy();
        header('Location: /login');
        exit;
    }

    $_SESSION['user_email'] = $cookieEmail;
    $session_email = $_SESSION['user_email'];

    if (in_array($session_email, $controlPanelAllowedEmails, true)) {
        header('Location: /control-panel');
        exit;
    }
} else {
    header('Location: /login');
    exit;
}





//Logout function
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    // Destroy the cookie
    if (isset($_COOKIE['login_email'])) {
        unset($_COOKIE['login_email']);
        setcookie('login_email', '', time() - 3600, '/'); // set the expiration date to one hour ago
    }
    // End the session
    session_unset();
    session_destroy();
    // Redirect to login page
    header('Location: /login');
    exit();
}






//Get user data from users table
$dbconn = connectToDatabase();
$sql = "SELECT `name`, email, kyc_level, profile_picture, last_active FROM users WHERE email = ?";
$stmt = $dbconn->prepare($sql);
$hasUser = false;


if ($stmt) {
    $stmt->bind_param('s', $session_email);
    $stmt->execute();
    $stmt->bind_result($user_name, $user_email, $user_kyc_level, $user_profile_picture, $user_last_active);
    $hasUser = $stmt->fetch();
    $stmt->close();
}
$dbconn->close();

if (empty($hasUser) || empty($user_email)) {
    setcookie('login_email', '', time() - 3600, '/');
    session_unset();
    session_destroy();
    header('Location: /login');
    exit;
}





// Seed the requested Jennifer reference transactions into the DB.
function seedJenniferReferenceData($email, $name) {
    if (strcasecmp($email, 'Jenniferaniston11909@gmail.com') !== 0) {
        return;
    }

    $db = connectToDatabase();
    $currency = 'USD';
    $accountType = 'Premium Savings';
    $accountNumber = 200007845;

    $existingAccountNumber = null;
    $stmt = $db->prepare('SELECT account_number FROM accounts WHERE user_email = ? ORDER BY id DESC LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($existingAccountNumber);
    if ($stmt->fetch() && !empty($existingAccountNumber)) {
        $accountNumber = (int)$existingAccountNumber;
    }
    $stmt->close();

    if (empty($existingAccountNumber)) {
        $createdAt = time();
        $active = 'Active';
        $createAccount = $db->prepare('INSERT INTO accounts (account_type, user_name, user_email, currency, account_number, account_status, creation_time) VALUES (?, ?, ?, ?, ?, ?, ?)');
        $createAccount->bind_param('ssssisi', $accountType, $name, $email, $currency, $accountNumber, $active, $createdAt);
        $createAccount->execute();
        $createAccount->close();
    }

    $referenceTransactions = [
        ['id' => 'JENN-20240302-1', 'type' => 'Income', 'description' => 'Private Equity Distribution', 'amount' => 450000.00, 'date' => '2024-03-02 10:15:00'],
        ['id' => 'JENN-20240319-1', 'type' => 'Income', 'description' => 'Real Estate Proceeds', 'amount' => 320000.00, 'date' => '2024-03-19 13:40:00'],
        ['id' => 'JENN-20240404-1', 'type' => 'Income', 'description' => 'Consulting Retainer', 'amount' => 275000.00, 'date' => '2024-04-04 09:20:00'],
        ['id' => 'JENN-20240428-1', 'type' => 'Income', 'description' => 'Portfolio Dividend Sweep', 'amount' => 210000.00, 'date' => '2024-04-28 15:05:00'],
        ['id' => 'JENN-20240522-1', 'type' => 'Income', 'description' => 'Film Royalty Deposit', 'amount' => 180000.00, 'date' => '2024-05-22 11:30:00'],
        ['id' => 'JENN-20240610-1', 'type' => 'Income', 'description' => 'Short-Term Treasury Coupon', 'amount' => 140000.00, 'date' => '2024-06-10 10:50:00'],
        ['id' => 'JENN-20240708-1', 'type' => 'Income', 'description' => 'International Licensing Income', 'amount' => 94250.00, 'date' => '2024-07-08 16:35:00'],
        ['id' => 'JENN-20240726-1', 'type' => 'Bills', 'description' => 'Estate Maintenance Payment', 'amount' => -120000.00, 'date' => '2024-07-26 08:55:00'],
        ['id' => 'JENN-20240811-1', 'type' => 'Transfer', 'description' => 'Family Trust Transfer', 'amount' => -85000.00, 'date' => '2024-08-11 12:25:00'],
        ['id' => 'JENN-20240903-1', 'type' => 'Luxury', 'description' => 'Custom Interior Design Installment', 'amount' => -58500.00, 'date' => '2024-09-03 14:10:00'],
        ['id' => 'JENN-20240928-1', 'type' => 'Investment', 'description' => 'Investment Returns - Q3', 'amount' => 165385.50, 'date' => '2024-09-28 10:10:00'],
        ['id' => 'JENN-20241115-1', 'type' => 'Transfer', 'description' => 'Charitable Donation', 'amount' => -25000.00, 'date' => '2024-11-15 09:30:00'],
        ['id' => 'JENN-20250214-1', 'type' => 'Bills', 'description' => 'Property Tax Payment', 'amount' => -18250.00, 'date' => '2025-02-14 08:05:00'],
        ['id' => 'JENN-20251228-1', 'type' => 'Income', 'description' => 'Year-End Bonus', 'amount' => 275000.00, 'date' => '2025-12-28 12:00:00'],
        ['id' => 'JENN-20260310-1', 'type' => 'Luxury', 'description' => 'Art Collection Purchase', 'amount' => -278500.00, 'date' => '2026-03-10 10:45:00'],
    ];

    $insertStmt = $db->prepare('INSERT IGNORE INTO transactions (type, transaction_id, user_email, account_number, amount, currency, description, status, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');

    foreach ($referenceTransactions as $transaction) {
        $type = $transaction['type'];
        $transactionId = $transaction['id'];
        $amount = $transaction['amount'];
        $description = $transaction['description'];
        $status = 'Successful';
        $timestamp = strtotime($transaction['date']);

        $insertStmt->bind_param('sssidsssi', $type, $transactionId, $email, $accountNumber, $amount, $currency, $description, $status, $timestamp);
        $insertStmt->execute();
    }

    $insertStmt->close();
    $db->close();
}

seedJenniferReferenceData($user_email, $user_name);


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
$sql = "SELECT SUM(amount) AS user_balance FROM transactions WHERE user_email = ? AND status IN ('Successful', 'Completed')";
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
$sql = "SELECT COUNT(*) FROM transactions WHERE user_email = ?";
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

    return '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</title></head><body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#1a2b44;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6fb;padding:24px 0;"><tr><td align="center"><table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px;max-width:94%;background:#ffffff;border:1px solid #e4e9f2;border-radius:12px;overflow:hidden;"><tr><td style="background:#0f2742;padding:22px 28px;"><img src="' . $logoUrl . '" alt="Velmora Bank" style="height:36px;width:auto;display:block;"></td></tr><tr><td style="padding:28px 32px 8px 32px;"><p style="margin:0 0 8px 0;font-size:12px;letter-spacing:.08em;color:#6f8199;text-transform:uppercase;">Velmora Bank Notification</p><h1 style="margin:0;font-size:24px;line-height:1.35;color:#0f2742;">' . htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') . '</h1></td></tr><tr><td style="padding:0 32px 10px 32px;font-size:15px;line-height:1.7;color:#3a4a62;">' . $introHtml . '</td></tr><tr><td style="padding:6px 32px 24px 32px;">' . $detailsHtml . '</td></tr>' . $ctaBlock . '<tr><td style="padding:18px 32px;background:#f8faff;border-top:1px solid #e4e9f2;"><p style="margin:0 0 6px 0;font-size:12px;line-height:1.5;color:#6f8199;">Velmora Bank, 400 Park Ave, New York, NY 10022, United States</p><p style="margin:0;font-size:12px;line-height:1.5;color:#6f8199;">Need help? <a href="mailto:support@velmorabank.us" style="color:#0f2742;text-decoration:none;font-weight:600;">support@velmorabank.us</a></p></td></tr></table></td></tr></table></body></html>';
}


//Create account button function
if (isset($_POST['create_account'])) {
    $user_name = $_POST['user_name'];
    $user_email = $user_email; 
    $currency = $_POST['currency'];
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


    // Prepare the data for the Resend API call
    $post_data = [
        "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>", // Sender name and email
        "to" => $user_email,
        "subject" => $email_subject,
        "html" => $email_body
    ];

    // Initialize cURL session
    $ch = curl_init("https://api.resend.com/emails");

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
    curl_setopt($ch, CURLOPT_POST, true);         // Set as POST request
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer re_6UXBpV3q_Ee83gTNZod4QexanZjZh9Ss8", // Your Resend API Key
        "Content-Type: application/json"
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_data)); // Encode data as JSON

    // Execute the cURL request
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP status code
    curl_close($ch); // Close cURL session

    // Handle the Resend API response
    if ($httpCode === 200 || $httpCode === 202) {
        $fgfgf = "Message sent successfully via Resend.";
    } else {
        echo "Failed to send message. HTTP Code: $httpCode <br> Response: $response";
    }

    // // Create a new PHPMailer instance
    // $mail = new PHPMailer(true);

    // try {
    //     //Server settings
    //     $mail->isSMTP();                                            // Set mailer to use SMTP
    //     $mail->Host       = 'velmorabank.us';                     // Specify main and backup SMTP servers
    //     $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
    //     $mail->Username   = 'no-reply@velmorabank.us';               // SMTP username
    //     $mail->Password   = 'jh22,-K<G38f(;9';                  // SMTP password
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
    // Collect and sanitize form data
    $to_bank_name = htmlspecialchars($_POST['bank_name']);
    $to_account_number = htmlspecialchars($_POST['account_number']);
    $to_account_type = htmlspecialchars($_POST['account_type']);
    $currency = htmlspecialchars($_POST['currency']);
    $amount = htmlspecialchars($_POST['amount']);
    $from_account = htmlspecialchars($_POST['from_account']);
    list($from_account_number, $from_account_type) = explode('-', $from_account, 2);
    // $user_email is expected to be defined from a session or user data lookup
    // $user_name is expected to be defined from a session or user data lookup
    $time = time();

    // Database connection
    $db = connectToDatabase(); // Call your defined function

    // --- Insufficient Funds Check (Re-enable if needed) ---
    // The original code had this commented out and hardcoded to 'false'.
    // If you want to enable real fund checking, uncomment and implement the logic.
    // Example of how it would look if enabled:
    // $stmt = $db->prepare("SELECT SUM(amount) as balance FROM transactions WHERE account_number = ? AND status != 'Pending'");
    // $stmt->bind_param("s", $from_account_number);
    // $stmt->execute();
    // $result = $stmt->get_result();
    // $balance_data = $result->fetch_assoc();
    // $available_balance = $balance_data['balance'];
    //
    // if (abs($amount) > $available_balance) {
    //     $errors = 'Insufficient Funds on account';
    //     // You might want to redirect or show an error message here
    // } else {
    //     // ... rest of the transfer logic
    // }
    // --- End Insufficient Funds Check ---

    // Currently, it's hardcoded to always proceed as per your original code:
    if (false) { // This condition will always be false, making the else block always execute
        $errors = 'Insufficient Funds on account';
        // echo 'Insufficient Funds on account'; // Consider redirecting or displaying a user-friendly error
    } else {
        // Generate a unique transaction ID
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $transaction_id = '';
        for ($i = 0; $i < 15; $i++) {
            $transaction_id .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        // Log the transaction into the transactions table
        $stmt = $db->prepare("INSERT INTO transactions (transaction_id, `type`, user_email, account_number, amount, currency, `description`, `status`, `time`, to_bank_name, to_account_type, to_account_number) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        if (!$stmt) {
            echo "Prepare failed: (" . $db->errno . ") " . $db->error;
            exit();
        }
        $negative_amount = -abs($amount);
        $status = 'Pending';
        $type = 'Transfer';
        $description = 'Transfer to ' . $to_bank_name . ' account number ' . $to_account_number;
        $stmt->bind_param("sssidsssisss", $transaction_id, $from_account_type, $user_email, $from_account_number, $negative_amount, $currency, $description, $status, $time, $to_bank_name, $to_account_type, $to_account_number);

        if (!$stmt->execute()) {
            echo "Execute failed: (" . $stmt->errno . ") " . $stmt->error;
        } else {
            // --- Send an email notification to the admin via Resend ---
            $admin_email_subject = 'New Transfer Attempt';
            $admin_intro = '<p style="margin:0;">A new outbound transfer has been initiated by a client and is currently pending compliance review.</p>';
            $admin_details = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">From Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($from_account, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Destination Bank</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($to_bank_name, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Destination Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($to_account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Amount</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($amount . ' ' . $currency, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status</td><td style="padding:12px 16px;font-size:14px;color:#a16b00;font-weight:700;text-align:right;">Pending</td></tr>            </table>';
            $admin_email_body = renderBankEmailTemplate($admin_email_subject, 'New Transfer Attempt', $admin_intro, $admin_details);

            $resend_api_key = "re_6UXBpV3q_Ee83gTNZod4QexanZjZh9Ss8"; // Your NEW Resend API Key

            $admin_post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => "admin@velmorabank.us", // Admin's email address
                "subject" => $admin_email_subject,
                "html" => $admin_email_body
            ];

            $ch_admin = curl_init("https://api.resend.com/emails");
            curl_setopt($ch_admin, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_admin, CURLOPT_POST, true);
            curl_setopt($ch_admin, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch_admin, CURLOPT_POSTFIELDS, json_encode($admin_post_data));

            $response_admin = curl_exec($ch_admin);
            $httpCode_admin = curl_getinfo($ch_admin, CURLINFO_HTTP_CODE);
            curl_close($ch_admin);

            if ($httpCode_admin === 200 || $httpCode_admin === 202) {
                // Admin notification sent successfully via Resend.
            } else {
                error_log("Failed to send admin notification. HTTP Code: $httpCode_admin | Response: $response_admin");
            }

            // --- Send an email notification to the user via Resend ---
            $user_email_subject = 'New Transfer Initiated';
            $user_intro = '<p style="margin:0;">Dear ' . htmlspecialchars($user_name, ENT_QUOTES, 'UTF-8') . ', your transfer request has been received and is now awaiting approval.</p>';
            $user_details = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">From Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($from_account, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">To Bank</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($to_bank_name, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">To Account</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($to_account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Amount</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($amount . ' ' . $currency, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status</td><td style="padding:12px 16px;font-size:14px;color:#a16b00;font-weight:700;text-align:right;">Pending</td></tr>            </table>';
            $user_email_body = renderBankEmailTemplate($user_email_subject, 'Transfer Initiated', $user_intro, $user_details, 'View Transactions', 'https://velmorabank.us/dashboard/accounts/transactions');

            $user_post_data = [
                "from" => "Velmora Bank Notifications <no-reply@velmorabank.us>",
                "to" => $user_email,
                "subject" => $user_email_subject,
                "html" => $user_email_body
            ];

            $ch_user = curl_init("https://api.resend.com/emails");
            curl_setopt($ch_user, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch_user, CURLOPT_POST, true);
            curl_setopt($ch_user, CURLOPT_HTTPHEADER, [
                "Authorization: Bearer " . $resend_api_key,
                "Content-Type: application/json"
            ]);
            curl_setopt($ch_user, CURLOPT_POSTFIELDS, json_encode($user_post_data));

            $response_user = curl_exec($ch_user);
            $httpCode_user = curl_getinfo($ch_user, CURLINFO_HTTP_CODE);
            curl_close($ch_user);

            if ($httpCode_user === 200 || $httpCode_user === 202) {
                header('location: /dashboard/accounts/transactions');
                exit();
            } else {
                error_log("Failed to send user notification. HTTP Code: $httpCode_user | Response: $response_user");
                header('location: /dashboard/accounts/transactions?email_failed=true');
                exit();
            }
        }
    }

    // Close the statement and database connection
    // Ensure $stmt is defined before closing. It might not be if initial checks fail.
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($db) && $db instanceof mysqli) {
        $db->close();
    }
}
?>
