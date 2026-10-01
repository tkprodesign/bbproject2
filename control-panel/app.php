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


function renderControlPanelBankEmail($subject, $headline, $introHtml, $detailsHtml) {
    $logoUrl = 'https://velmorabank.us/assets/images/branding/logo.png';
    return '<!DOCTYPE html><html lang="en"><head>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>' . htmlspecialchars($subject, ENT_QUOTES, 'UTF-8') . '</title></head><body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#1a2b44;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6fb;padding:24px 0;"><tr><td align="center"><table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0" style="width:640px;max-width:94%;background:#ffffff;border:1px solid #e4e9f2;border-radius:12px;overflow:hidden;"><tr><td style="background:#0f2742;padding:22px 28px;"><img src="' . $logoUrl . '" alt="Velmora Bank" style="height:36px;width:auto;display:block;"></td></tr><tr><td style="padding:28px 32px 8px 32px;"><p style="margin:0 0 8px 0;font-size:12px;letter-spacing:.08em;color:#6f8199;text-transform:uppercase;">Velmora Bank Notification</p><h1 style="margin:0;font-size:24px;line-height:1.35;color:#0f2742;">' . htmlspecialchars($headline, ENT_QUOTES, 'UTF-8') . '</h1></td></tr><tr><td style="padding:0 32px 10px 32px;font-size:15px;line-height:1.7;color:#3a4a62;">' . $introHtml . '</td></tr><tr><td style="padding:6px 32px 24px 32px;">' . $detailsHtml . '</td></tr><tr><td style="padding:18px 32px;background:#f8faff;border-top:1px solid #e4e9f2;"><p style="margin:0 0 6px 0;font-size:12px;line-height:1.5;color:#6f8199;">Velmora Bank, 400 Park Ave, New York, NY 10022, United States</p><p style="margin:0;font-size:12px;line-height:1.5;color:#6f8199;">Need help? <a href="mailto:support@velmorabank.us" style="color:#0f2742;text-decoration:none;font-weight:600;">support@velmorabank.us</a></p></td></tr></table></td></tr></table></body></html>';
}


require_once $baseDir . '/common-sections/backend-auth.php';
velmoraRequireBackendRole('admin');
$backendIdentity = velmoraBackendSession();
$_SESSION['user_email'] = strtolower((string)($backendIdentity['email'] ?? ''));
$session_email = $_SESSION['user_email'];

normalizeLegacyTransactionStatuses();











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

        try {
            if (ensureDynamicDataTable($db)) {
                $stmt = $db->prepare("INSERT INTO dynamic_data (`name`, `value`) VALUES ('phone_number', ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

                if ($stmt) {
                    $stmt->bind_param('s', $supportPhone);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        } catch (mysqli_sql_exception $exception) {
            error_log('Unable to update support phone number: ' . $exception->getMessage());
        }

        $db->close();
    }

    header('Location: /control-panel');
    exit;
}

// Deposit into user account
if (isset($_POST['credit_user'])) {
    $user_email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $account_number = (int) preg_replace('/\D+/', '', (string)($_POST['account_number'] ?? ''));
    $input_amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $input_currency = strtoupper(trim((string)($_POST['currency'] ?? '')));
    $description = trim((string)($_POST['description'] ?? ''));
    $transaction_type = 'Deposit';
    $status = 'Successful';
    $time = time();

    if (!$user_email || !$account_number || $input_amount === false || $input_amount <= 0 || !velmoraIsSupportedCurrency($input_currency)) {
        header('Location: /control-panel?credit_user=invalid');
        exit;
    }

    $dbconn = connectToDatabase();
    $accountStmt = $dbconn->prepare("SELECT currency FROM accounts WHERE user_email = ? AND account_number = ? LIMIT 1");
    if (!$accountStmt) {
        $dbconn->close();
        header('Location: /control-panel?credit_user=failed');
        exit;
    }
    $accountStmt->bind_param('si', $user_email, $account_number);
    $accountStmt->execute();
    $accountStmt->bind_result($account_currency);
    $accountExists = $accountStmt->fetch();
    $accountStmt->close();

    if (!$accountExists || !velmoraIsSupportedCurrency((string)$account_currency)) {
        $dbconn->close();
        header('Location: /control-panel?credit_user=account_mismatch');
        exit;
    }

    $account_currency = strtoupper((string)$account_currency);
    try {
        $fx = velmoraFxQuote((float)$input_amount, $input_currency, $account_currency);
    } catch (Throwable $e) {
        $dbconn->close();
        header('Location: /control-panel?credit_user=invalid');
        exit;
    }

    $credited_amount = (float)$fx['amount_out'];
    $fx_rate = (float)$fx['customer_rate'];
    $fx_spread_bps = (int)$fx['spread_bps'];
    $description = $description !== '' ? $description : 'Manual deposit by control panel';

    do {
        $transaction_id = bin2hex(random_bytes(8));
        $stmtCheck = $dbconn->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_id = ?");
        $stmtCheck->bind_param('s', $transaction_id);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();
    } while ((int)$count > 0);

    $stmt = $dbconn->prepare("INSERT INTO transactions
        (`type`, transaction_id, user_email, account_number, amount, currency, `description`, `status`, `time`,
         counter_currency, counter_amount, fx_rate, fx_spread_bps)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if (!$stmt) {
        $dbconn->close();
        header('Location: /control-panel?credit_user=failed');
        exit;
    }

    $stmt->bind_param(
        'sssidsssisddi',
        $transaction_type,
        $transaction_id,
        $user_email,
        $account_number,
        $credited_amount,
        $account_currency,
        $description,
        $status,
        $time,
        $input_currency,
        $input_amount,
        $fx_rate,
        $fx_spread_bps
    );

    if (!$stmt->execute()) {
        error_log('Deposit insert failed: ' . $stmt->error);
        $stmt->close();
        $dbconn->close();
        header('Location: /control-panel?credit_user=failed');
        exit;
    }

    $stmt->close();
    $dbconn->close();

    $formatted_time = date('H:i | d F Y /T', $time);
    $receivedDisplay = velmoraFormatCurrency((float)$input_amount, $input_currency);
    $creditedDisplay = velmoraFormatCurrency($credited_amount, $account_currency);
    $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Transaction ID</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($transaction_id, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Deposit Received</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($receivedDisplay, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Account Credited</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($creditedDisplay, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Bank FX Rate</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">1 ' . htmlspecialchars($input_currency, ENT_QUOTES, 'UTF-8') . ' = ' . htmlspecialchars(number_format($fx_rate, 6), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($account_currency, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;">Status / Time</td><td style="padding:12px 16px;text-align:right;font-weight:700;">' . htmlspecialchars($status . ' • ' . $formatted_time, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '</table>';
    $email_subject = 'Deposit Confirmation - Velmora Bank';
    $email_body = renderControlPanelBankEmail($email_subject, 'Deposit Confirmation', '<p style="margin:0;">A deposit has been posted to your account successfully.</p>', $detailsHtml);
    if (!sendSiteEmail($user_email, $email_subject, $email_body)) {
        error_log('Failed to send deposit confirmation email via SMTP.');
    }

    header('Location: /control-panel?credit_user=success');
    exit;
}

// Withdraw from user account
if (isset($_POST['debit_user'])) {
    $user_email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $account_number = (int) preg_replace('/\D+/', '', (string)($_POST['account_number'] ?? ''));
    $payout_amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);
    $payout_currency = strtoupper(trim((string)($_POST['currency'] ?? '')));
    $description = trim((string)($_POST['description'] ?? ''));
    $transaction_type = 'Withdrawal';
    $status = 'Successful';
    $time = time();

    if (!$user_email || !$account_number || $payout_amount === false || $payout_amount <= 0 || !velmoraIsSupportedCurrency($payout_currency)) {
        header('Location: /control-panel?debit_user=invalid');
        exit;
    }

    $dbconn = connectToDatabase();
    $accountStmt = $dbconn->prepare("SELECT currency FROM accounts WHERE user_email = ? AND account_number = ? LIMIT 1");
    $accountStmt->bind_param('si', $user_email, $account_number);
    $accountStmt->execute();
    $accountStmt->bind_result($account_currency);
    $accountExists = $accountStmt->fetch();
    $accountStmt->close();

    if (!$accountExists || !velmoraIsSupportedCurrency((string)$account_currency)) {
        $dbconn->close();
        header('Location: /control-panel?debit_user=invalid');
        exit;
    }

    $account_currency = strtoupper((string)$account_currency);
    try {
        $fx = velmoraFxRequiredSource((float)$payout_amount, $account_currency, $payout_currency);
    } catch (Throwable $e) {
        $dbconn->close();
        header('Location: /control-panel?debit_user=invalid');
        exit;
    }

    $debit_amount = (float)$fx['amount_in'];
    $fx_rate = (float)$fx['customer_rate'];
    $fx_spread_bps = (int)$fx['spread_bps'];

    $balanceStmt = $dbconn->prepare("SELECT COALESCE(SUM(amount), 0) FROM transactions WHERE account_number = ? AND (status IS NULL OR LOWER(status) <> 'failed')");
    $balanceStmt->bind_param('i', $account_number);
    $balanceStmt->execute();
    $balanceStmt->bind_result($available_balance);
    $balanceStmt->fetch();
    $balanceStmt->close();

    if ($debit_amount > (float)$available_balance) {
        $dbconn->close();
        header('Location: /control-panel?debit_user=insufficient');
        exit;
    }

    $description = $description !== '' ? $description : 'Manual withdrawal by control panel';

    do {
        $transaction_id = bin2hex(random_bytes(8));
        $stmtCheck = $dbconn->prepare("SELECT COUNT(*) FROM transactions WHERE transaction_id = ?");
        $stmtCheck->bind_param('s', $transaction_id);
        $stmtCheck->execute();
        $stmtCheck->bind_result($count);
        $stmtCheck->fetch();
        $stmtCheck->close();
    } while ((int)$count > 0);

    $negative_amount = -abs($debit_amount);
    $stmt = $dbconn->prepare("INSERT INTO transactions
        (`type`, transaction_id, user_email, account_number, amount, currency, `description`, `status`, `time`,
         counter_currency, counter_amount, fx_rate, fx_spread_bps)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param(
        'sssidsssisddi',
        $transaction_type,
        $transaction_id,
        $user_email,
        $account_number,
        $negative_amount,
        $account_currency,
        $description,
        $status,
        $time,
        $payout_currency,
        $payout_amount,
        $fx_rate,
        $fx_spread_bps
    );

    if (!$stmt->execute()) {
        error_log('Withdrawal insert failed: ' . $stmt->error);
        $stmt->close();
        $dbconn->close();
        header('Location: /control-panel?debit_user=failed');
        exit;
    }

    $stmt->close();
    $dbconn->close();

    $formatted_time = date('H:i | d F Y /T', $time);
    $debitDisplay = velmoraFormatCurrency($debit_amount, $account_currency);
    $payoutDisplay = velmoraFormatCurrency((float)$payout_amount, $payout_currency);
    $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Transaction ID</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($transaction_id, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Account Debited</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($debitDisplay, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Payout Amount</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">' . htmlspecialchars($payoutDisplay, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;">Bank FX Rate</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;text-align:right;font-weight:700;">1 ' . htmlspecialchars($account_currency, ENT_QUOTES, 'UTF-8') . ' = ' . htmlspecialchars(number_format($fx_rate, 6), ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($payout_currency, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '<tr><td style="padding:12px 16px;">Status / Time</td><td style="padding:12px 16px;text-align:right;font-weight:700;">' . htmlspecialchars($status . ' • ' . $formatted_time, ENT_QUOTES, 'UTF-8') . '</td></tr>'
        . '</table>';
    $email_subject = 'Withdrawal Confirmation - Velmora Bank';
    $email_body = renderControlPanelBankEmail($email_subject, 'Withdrawal Confirmation', '<p style="margin:0;">Your withdrawal has been processed successfully.</p>', $detailsHtml);
    if (!sendSiteEmail($user_email, $email_subject, $email_body)) {
        error_log('Failed to send withdrawal confirmation email via SMTP.');
    }

    header('Location: /control-panel?debit_user=success');
    exit;
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
            // Send an email notification based on the decision using SpaceMail SMTP
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

            $introHtml = '<p style="margin:0;">Dear Valued Customer, ' . htmlspecialchars($email_message, ENT_QUOTES, 'UTF-8') . '</p>';
            $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Transaction ID</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($transaction_id, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Account Number</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($account_number, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Amount</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($currency . ' ' . $amount_display, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Description</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($description, ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Status / Time</td><td style="padding:12px 16px;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($status . ' • ' . $formatted_time, ENT_QUOTES, 'UTF-8') . '</td></tr>            </table>';
            $email_body = renderControlPanelBankEmail($email_subject, $email_heading, $introHtml, $detailsHtml);

            if (!sendSiteEmail($user_email, $email_subject, $email_body)) {
                error_log('Failed to send withdrawal ' . strtolower($decision) . ' email via SMTP.');
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

            // --- Send KYC Notification Email via SpaceMail SMTP ---
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

            $introHtml = '<p style="margin:0;">Dear ' . htmlspecialchars($full_name, ENT_QUOTES, 'UTF-8') . ',</p>' . $email_message;
            $detailsHtml = '<table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Verification Type</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">KYC Review</td></tr>                <tr><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Decision</td><td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars(ucfirst($decision), ENT_QUOTES, 'UTF-8') . '</td></tr>                <tr><td style="padding:12px 16px;font-size:13px;color:#6f8199;">Reference</td><td style="padding:12px 16px;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . htmlspecialchars($user_email, ENT_QUOTES, 'UTF-8') . '</td></tr>            </table>';
            $email_body = renderControlPanelBankEmail($email_subject, $email_heading, $introHtml, $detailsHtml);

            if (!sendSiteEmail($user_email, $email_subject, $email_body)) {
                error_log('Failed to send KYC ' . strtolower($decision) . ' email via SMTP.');
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
