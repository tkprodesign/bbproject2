<?php
require_once __DIR__ . '/../dashboard/app.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['v3_csrf'])) {
    $_SESSION['v3_csrf'] = bin2hex(random_bytes(24));
}

function v3CsrfInput(): string {
    return '<input type="hidden" name="v3_csrf" value="' . htmlspecialchars((string)$_SESSION['v3_csrf'], ENT_QUOTES, 'UTF-8') . '">';
}

function v3VerifyPost(): void {
    $token = (string)($_POST['v3_csrf'] ?? '');
    if ($token === '' || !hash_equals((string)($_SESSION['v3_csrf'] ?? ''), $token)) {
        http_response_code(419);
        exit('Session validation failed. Please refresh and try again.');
    }
}

function v3Redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}

function v3TransactionId(string $prefix): string {
    return strtoupper($prefix) . '-' . strtoupper(bin2hex(random_bytes(8)));
}

function v3Accounts(string $email): array {
    $db = connectToDatabase();
    $stmt = $db->prepare("SELECT a.id, a.account_number, a.account_type, a.currency, a.account_status, a.creation_time,
        COALESCE(SUM(CASE WHEN t.status IS NULL OR LOWER(t.status) <> 'failed' THEN t.amount ELSE 0 END), 0) AS balance
        FROM accounts a
        LEFT JOIN transactions t ON t.account_number = a.account_number
        WHERE a.user_email = ?
        GROUP BY a.id, a.account_number, a.account_type, a.currency, a.account_status, a.creation_time
        ORDER BY a.id DESC");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $rows = [];
    while ($row = $result->fetch_assoc()) {
        $row['currency'] = strtoupper((string)$row['currency']);
        $row['balance'] = (float)$row['balance'];
        $rows[] = $row;
    }
    $stmt->close();
    $db->close();
    return $rows;
}

function v3Profile(string $email, string $fallbackName): array {
    $profile = [
        'name' => $fallbackName,
        'dob' => 'Not available',
        'occupation' => 'Not available',
        'status' => 'Not submitted',
        'address' => 'Not available',
        'city' => 'Not available',
        'country' => 'Not available',
        'phone' => 'Not available',
        'nationality' => 'Not available',
        'source_of_income' => 'Not available',
    ];

    $db = connectToDatabase();
    $stmt = $db->prepare("SELECT first_name, middle_name, last_name, date_of_birth, occupation, status,
        address1, city, country_of_residence, phone_number, nationality, source_of_income
        FROM kyc_data WHERE email = ? ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($first, $middle, $last, $dob, $occupation, $status, $address, $city, $country, $phone, $nationality, $source);
    if ($stmt->fetch()) {
        $full = trim(implode(' ', array_filter([$first, $middle, $last])));
        if ($full !== '') $profile['name'] = $full;
        foreach ([
            'dob' => $dob, 'occupation' => $occupation, 'status' => $status, 'address' => $address,
            'city' => $city, 'country' => $country, 'phone' => $phone,
            'nationality' => $nationality, 'source_of_income' => $source
        ] as $key => $value) {
            if ($value !== null && trim((string)$value) !== '') $profile[$key] = (string)$value;
        }
    }
    $stmt->close();
    $db->close();
    return $profile;
}

function v3OwnedAccount(mysqli $db, string $email, string $accountNumber): ?array {
    $stmt = $db->prepare("SELECT account_number, account_type, currency, account_status
        FROM accounts WHERE user_email = ? AND account_number = ? LIMIT 1");
    $stmt->bind_param('ss', $email, $accountNumber);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc() ?: null;
    $stmt->close();
    if ($row) $row['currency'] = strtoupper((string)$row['currency']);
    return $row;
}

function v3AccountBalance(mysqli $db, string $accountNumber): float {
    $stmt = $db->prepare("SELECT COALESCE(SUM(CASE WHEN status IS NULL OR LOWER(status) <> 'failed' THEN amount ELSE 0 END), 0)
        FROM transactions WHERE account_number = ?");
    $stmt->bind_param('s', $accountNumber);
    $stmt->execute();
    $stmt->bind_result($balance);
    $stmt->fetch();
    $stmt->close();
    return (float)$balance;
}

function v3PostMessage(string $key, string $value): void {
    $_SESSION['v3_flash'][$key] = $value;
}

function v3Flash(string $key): ?string {
    $value = $_SESSION['v3_flash'][$key] ?? null;
    unset($_SESSION['v3_flash'][$key]);
    return is_string($value) ? $value : null;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['v3_create_account'])) {
    v3VerifyPost();
    $currency = strtoupper(trim((string)($_POST['currency'] ?? '')));
    $accountType = trim((string)($_POST['account_type'] ?? ''));

    if (!velmoraIsSupportedCurrency($currency) || !in_array($accountType, ['Savings','Current','Fixed','Personal Checking'], true)) {
        v3PostMessage('error', 'Choose a valid account type and currency.');
        v3Redirect('/dashboard-v3/accounts/');
    }

    $db = connectToDatabase();
    do {
        $accountNumber = (string)random_int(2000000000, 2999999999);
        $stmt = $db->prepare('SELECT COUNT(*) FROM accounts WHERE account_number = ?');
        $stmt->bind_param('s', $accountNumber);
        $stmt->execute();
        $stmt->bind_result($taken);
        $stmt->fetch();
        $stmt->close();
    } while ((int)$taken > 0);

    $status = 'Active';
    $now = time();
    $stmt = $db->prepare('INSERT INTO accounts (account_type,user_name,user_email,currency,account_number,account_status,creation_time) VALUES (?,?,?,?,?,?,?)');
    $stmt->bind_param('ssssssi', $accountType, $user_name, $user_email, $currency, $accountNumber, $status, $now);
    $stmt->execute();
    $stmt->close();
    $db->close();

    v3Redirect('/dashboard-v3/accounts/opened/?account=' . urlencode($accountNumber));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['v3_quote_exchange'])) {
    v3VerifyPost();
    $from = preg_replace('/\D+/', '', (string)($_POST['from_account'] ?? ''));
    $to = preg_replace('/\D+/', '', (string)($_POST['to_account'] ?? ''));
    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);

    $db = connectToDatabase();
    $source = v3OwnedAccount($db, $user_email, $from);
    $target = v3OwnedAccount($db, $user_email, $to);

    if (!$source || !$target || $source['account_status'] !== 'Active' || $target['account_status'] !== 'Active'
        || $from === $to || $source['currency'] === $target['currency'] || $amount === false || $amount <= 0) {
        $db->close();
        v3PostMessage('error', 'Choose two active accounts in different currencies and enter a valid amount.');
        v3Redirect('/dashboard-v3/exchange/');
    }

    $balance = v3AccountBalance($db, $from);
    $db->close();
    if ((float)$amount > $balance) {
        v3PostMessage('error', 'The source account does not have enough available funds for this trade.');
        v3Redirect('/dashboard-v3/exchange/');
    }

    try {
        $fx = velmoraFxQuote((float)$amount, $source['currency'], $target['currency']);
    } catch (Throwable $e) {
        v3PostMessage('error', 'A bank exchange quote could not be prepared for that currency pair.');
        v3Redirect('/dashboard-v3/exchange/');
    }

    $quoteId = 'Q-' . strtoupper(bin2hex(random_bytes(6)));
    $_SESSION['v3_fx_quote'] = [
        'quote_id' => $quoteId,
        'from_account' => $from,
        'to_account' => $to,
        'source_currency' => $source['currency'],
        'target_currency' => $target['currency'],
        'source_amount' => (float)$amount,
        'target_amount' => (float)$fx['amount_out'],
        'customer_rate' => (float)$fx['customer_rate'],
        'spread_bps' => (int)$fx['spread_bps'],
        'quoted_at' => time(),
        'expires_at' => time() + 60,
    ];
    v3Redirect('/dashboard-v3/exchange/?review=1');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['v3_execute_exchange'])) {
    v3VerifyPost();
    $quote = $_SESSION['v3_fx_quote'] ?? null;
    $quoteId = (string)($_POST['quote_id'] ?? '');

    if (!is_array($quote) || $quoteId === '' || !hash_equals((string)$quote['quote_id'], $quoteId) || time() > (int)$quote['expires_at']) {
        unset($_SESSION['v3_fx_quote']);
        v3PostMessage('error', 'That exchange quote has expired. Request a fresh quote.');
        v3Redirect('/dashboard-v3/exchange/');
    }

    $db = connectToDatabase();
    $db->begin_transaction();
    try {
        $source = v3OwnedAccount($db, $user_email, (string)$quote['from_account']);
        $target = v3OwnedAccount($db, $user_email, (string)$quote['to_account']);
        if (!$source || !$target || $source['currency'] !== $quote['source_currency'] || $target['currency'] !== $quote['target_currency']) {
            throw new RuntimeException('Account details changed after the quote.');
        }

        $balance = v3AccountBalance($db, (string)$quote['from_account']);
        if ((float)$quote['source_amount'] > $balance) {
            throw new RuntimeException('Insufficient funds at execution.');
        }

        $tradeId = 'FX-' . strtoupper(bin2hex(random_bytes(8)));
        $debitId = $tradeId . '-D';
        $creditId = $tradeId . '-C';
        $status = 'Successful';
        $type = 'FX Exchange';
        $now = time();

        $sourceAmount = -abs((float)$quote['source_amount']);
        $targetAmount = abs((float)$quote['target_amount']);
        $sourceCurrency = (string)$quote['source_currency'];
        $targetCurrency = (string)$quote['target_currency'];
        $rate = (float)$quote['customer_rate'];
        $spread = (int)$quote['spread_bps'];
        $sourceDesc = 'Currency exchange to ' . $targetCurrency . ' • ' . $tradeId;
        $targetDesc = 'Currency exchange from ' . $sourceCurrency . ' • ' . $tradeId;
        $sourceAccount = (string)$quote['from_account'];
        $targetAccount = (string)$quote['to_account'];

        $stmt = $db->prepare("INSERT INTO transactions
            (transaction_id,type,user_email,account_number,amount,currency,description,status,time,counter_currency,counter_amount,fx_rate,fx_spread_bps)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssidsssisddi', $debitId, $type, $user_email, $sourceAccount, $sourceAmount, $sourceCurrency, $sourceDesc, $status, $now, $targetCurrency, $targetAmount, $rate, $spread);
        $stmt->execute();
        $stmt->close();

        $stmt = $db->prepare("INSERT INTO transactions
            (transaction_id,type,user_email,account_number,amount,currency,description,status,time,counter_currency,counter_amount,fx_rate,fx_spread_bps)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $counterSource = abs((float)$quote['source_amount']);
        $stmt->bind_param('sssidsssisddi', $creditId, $type, $user_email, $targetAccount, $targetAmount, $targetCurrency, $targetDesc, $status, $now, $sourceCurrency, $counterSource, $rate, $spread);
        $stmt->execute();
        $stmt->close();

        $tradeStatus = 'Executed';
        $quotedAt = (int)$quote['quoted_at'];
        $sourcePositive = abs((float)$quote['source_amount']);

        $stmt = $db->prepare("INSERT INTO fx_trades
            (trade_id,user_email,from_account_number,to_account_number,source_currency,target_currency,source_amount,target_amount,customer_rate,fx_spread_bps,status,quoted_at,executed_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssssssdddisii', $tradeId, $user_email, $sourceAccount, $targetAccount, $sourceCurrency, $targetCurrency, $sourcePositive, $targetAmount, $rate, $spread, $tradeStatus, $quotedAt, $now);
        $stmt->execute();
        $stmt->close();

        $db->commit();
        unset($_SESSION['v3_fx_quote']);
        v3PostMessage('success', 'Exchange executed. Trade reference: ' . $tradeId);
    } catch (Throwable $e) {
        $db->rollback();
        v3PostMessage('error', 'Exchange could not be executed: ' . $e->getMessage());
    } finally {
        $db->close();
    }
    v3Redirect('/dashboard-v3/exchange/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['v3_quote_transfer'])) {
    v3VerifyPost();
    $from = preg_replace('/\D+/', '', (string)($_POST['from_account'] ?? ''));
    $bank = trim((string)($_POST['bank_name'] ?? ''));
    $recipient = trim((string)($_POST['account_number'] ?? ''));
    $accountType = trim((string)($_POST['account_type'] ?? ''));
    $recipientCurrency = strtoupper(trim((string)($_POST['currency'] ?? '')));
    $amount = filter_var($_POST['amount'] ?? null, FILTER_VALIDATE_FLOAT);

    $db = connectToDatabase();
    $source = v3OwnedAccount($db, $user_email, $from);
    if (!$source || $source['account_status'] !== 'Active' || $bank === '' || $recipient === '' || $amount === false || $amount <= 0 || !velmoraIsSupportedCurrency($recipientCurrency)) {
        $db->close();
        v3PostMessage('error', 'Complete all transfer details correctly.');
        v3Redirect('/dashboard-v3/transfer/');
    }

    $balance = v3AccountBalance($db, $from);
    $db->close();
    if ((float)$amount > $balance) {
        v3PostMessage('error', 'Insufficient available funds in the selected account.');
        v3Redirect('/dashboard-v3/transfer/');
    }

    try {
        $fx = velmoraFxQuote((float)$amount, $source['currency'], $recipientCurrency);
    } catch (Throwable $e) {
        v3PostMessage('error', 'A transfer quote could not be prepared.');
        v3Redirect('/dashboard-v3/transfer/');
    }

    $_SESSION['v3_transfer_quote'] = [
        'quote_id' => 'TQ-' . strtoupper(bin2hex(random_bytes(6))),
        'from_account' => $from,
        'source_currency' => $source['currency'],
        'amount' => (float)$amount,
        'bank_name' => $bank,
        'recipient_account' => $recipient,
        'recipient_account_type' => $accountType ?: 'Not Sure',
        'recipient_currency' => $recipientCurrency,
        'recipient_amount' => (float)$fx['amount_out'],
        'customer_rate' => (float)$fx['customer_rate'],
        'spread_bps' => (int)$fx['spread_bps'],
        'expires_at' => time() + 60,
    ];
    v3Redirect('/dashboard-v3/transfer/?review=1');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['v3_execute_transfer'])) {
    v3VerifyPost();
    $quote = $_SESSION['v3_transfer_quote'] ?? null;
    $quoteId = (string)($_POST['quote_id'] ?? '');

    if (!is_array($quote) || $quoteId === '' || !hash_equals((string)$quote['quote_id'], $quoteId) || time() > (int)$quote['expires_at']) {
        unset($_SESSION['v3_transfer_quote']);
        v3PostMessage('error', 'That transfer quote expired. Please review a fresh quote.');
        v3Redirect('/dashboard-v3/transfer/');
    }

    $db = connectToDatabase();
    try {
        $source = v3OwnedAccount($db, $user_email, (string)$quote['from_account']);
        if (!$source || $source['currency'] !== $quote['source_currency']) throw new RuntimeException('Source account changed.');
        if ((float)$quote['amount'] > v3AccountBalance($db, (string)$quote['from_account'])) throw new RuntimeException('Insufficient funds.');

        $txid = v3TransactionId('TRF');
        $type = 'Transfer';
        $status = 'Pending';
        $amount = -abs((float)$quote['amount']);
        $now = time();
        $description = 'Transfer to ' . $quote['bank_name'] . ' account number ' . $quote['recipient_account'];
        $sourceAccount = (string)$quote['from_account'];
        $sourceCurrency = (string)$quote['source_currency'];
        $recipientCurrency = (string)$quote['recipient_currency'];
        $recipientAmount = (float)$quote['recipient_amount'];
        $rate = (float)$quote['customer_rate'];
        $spread = (int)$quote['spread_bps'];
        $bank = (string)$quote['bank_name'];
        $acctType = (string)$quote['recipient_account_type'];
        $recipient = (string)$quote['recipient_account'];

        $stmt = $db->prepare("INSERT INTO transactions
            (transaction_id,type,user_email,account_number,amount,currency,description,status,time,to_bank_name,to_account_type,to_account_number,counter_currency,counter_amount,fx_rate,fx_spread_bps)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('sssidsssissssddi', $txid, $type, $user_email, $sourceAccount, $amount, $sourceCurrency, $description, $status, $now, $bank, $acctType, $recipient, $recipientCurrency, $recipientAmount, $rate, $spread);
        $stmt->execute();
        $stmt->close();
        $db->close();

        unset($_SESSION['v3_transfer_quote']);
        v3PostMessage('success', 'Transfer submitted for bank processing. Reference: ' . $txid);
    } catch (Throwable $e) {
        $db->close();
        v3PostMessage('error', 'Transfer could not be submitted: ' . $e->getMessage());
    }
    v3Redirect('/dashboard-v3/transfer/');
}
