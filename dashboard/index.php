<?php include('app.php') ?>
<?php
$pending_transactions = 0;
$successful_transactions = 0;
$latest_transaction_time = null;
$active_accounts = 0;
$kyc_status_label = 'Not Submitted';

$dashboardRows = [];
$dashboardMeta = [
    'account_holder' => $user_name,
    'date_of_birth' => 'Not available',
    'account_number' => 'Not available',
    'account_type' => 'Primary',
    'profession' => !empty($is_demo_account) ? 'Doctor' : 'Not available',
];

$requestedDisplayCurrency = strtoupper(trim((string)($_GET['display_currency'] ?? '')));
if ($requestedDisplayCurrency !== '' && velmoraIsSupportedCurrency($requestedDisplayCurrency)) {
    $_SESSION['display_currency'] = $requestedDisplayCurrency;
}
$displayCurrency = strtoupper(trim((string)($_SESSION['display_currency'] ?? '')));
if (!velmoraIsSupportedCurrency($displayCurrency)) {
    $displayCurrency = '';
}

$dbMetrics = connectToDatabase();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND status = 'Pending'");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($pending_transactions);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND status IN ('Successful')");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($successful_transactions);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT MAX(`time`) FROM transactions WHERE user_email = ?");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($latest_transaction_time);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM accounts WHERE user_email = ? AND account_status = 'Active'");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($active_accounts);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT status, date_of_birth, first_name, middle_name, last_name FROM kyc_data WHERE email = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($latest_kyc_status, $kyc_dob, $kyc_first_name, $kyc_middle_name, $kyc_last_name);
if ($stmt->fetch()) {
    if (!empty($latest_kyc_status)) {
        $kyc_status_label = $latest_kyc_status;
    }
    if (!empty($kyc_dob)) {
        $dashboardMeta['date_of_birth'] = $kyc_dob;
    }
    $kycFullName = trim(implode(' ', array_filter([$kyc_first_name, $kyc_middle_name, $kyc_last_name])));
    if ($kycFullName !== '') {
        $dashboardMeta['account_holder'] = $kycFullName;
    }
}
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT account_number, account_type, currency FROM accounts WHERE user_email = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($meta_account_number, $meta_account_type, $meta_account_currency);
if ($stmt->fetch()) {
    $dashboardMeta['account_number'] = '****' . substr((string)$meta_account_number, -4);
    $dashboardMeta['account_type'] = $meta_account_type;
    if ($displayCurrency === '' && velmoraIsSupportedCurrency((string)$meta_account_currency)) {
        $displayCurrency = strtoupper((string)$meta_account_currency);
        $_SESSION['display_currency'] = $displayCurrency;
    }
}
$stmt->close();

if ($displayCurrency === '') {
    $displayCurrency = 'USD';
    $_SESSION['display_currency'] = $displayCurrency;
}

$currentAccountBalances = [];
$dashboardBalance = 0.0;
$balanceStmt = $dbMetrics->prepare("
    SELECT a.account_number, a.currency,
           COALESCE(SUM(CASE WHEN t.status IS NULL OR LOWER(t.status) <> 'failed' THEN t.amount ELSE 0 END), 0) AS balance
    FROM accounts a
    LEFT JOIN transactions t ON t.account_number = a.account_number
    WHERE a.user_email = ?
    GROUP BY a.account_number, a.currency
");
$balanceStmt->bind_param('s', $user_email);
$balanceStmt->execute();
$balanceResult = $balanceStmt->get_result();
while ($balanceRow = $balanceResult->fetch_assoc()) {
    $accountCurrency = strtoupper((string)$balanceRow['currency']);
    if (!velmoraIsSupportedCurrency($accountCurrency)) {
        $accountCurrency = 'USD';
    }
    $accountBalance = (float)$balanceRow['balance'];
    $currentAccountBalances[(string)$balanceRow['account_number']] = [
        'amount' => $accountBalance,
        'currency' => $accountCurrency,
    ];
    try {
        $dashboardBalance += velmoraConvertForValuation($accountBalance, $accountCurrency, $displayCurrency);
    } catch (Throwable $e) {
        // Ignore unsupported legacy rows rather than mixing currencies incorrectly.
    }
}
$balanceStmt->close();

$totalCredits = 0.0;
$totalDebits = 0.0;
$creditCount = 0;
$debitCount = 0;
$summaryStmt = $dbMetrics->prepare("SELECT amount, currency FROM transactions WHERE user_email = ? AND (status IS NULL OR LOWER(status) <> 'failed')");
$summaryStmt->bind_param('s', $user_email);
$summaryStmt->execute();
$summaryStmt->bind_result($summaryAmount, $summaryCurrency);
while ($summaryStmt->fetch()) {
    $rowCurrency = strtoupper((string)$summaryCurrency);
    if (!velmoraIsSupportedCurrency($rowCurrency)) {
        $rowCurrency = 'USD';
    }
    try {
        $converted = velmoraConvertForValuation(abs((float)$summaryAmount), $rowCurrency, $displayCurrency);
    } catch (Throwable $e) {
        continue;
    }
    if ((float)$summaryAmount >= 0) {
        $totalCredits += $converted;
        $creditCount++;
    } else {
        $totalDebits += $converted;
        $debitCount++;
    }
}
$summaryStmt->close();

$stmt = $dbMetrics->prepare("SELECT account_number, type, description, amount, currency, counter_currency, counter_amount, fx_rate, status, `time` FROM transactions WHERE user_email = ? ORDER BY `time` DESC LIMIT 10");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($tx_account_number, $tx_type, $tx_description, $tx_amount, $tx_currency, $tx_counter_currency, $tx_counter_amount, $tx_fx_rate, $tx_status, $tx_time);
while ($stmt->fetch()) {
    $normalizedType = strtolower(trim((string)$tx_type));
    if ($normalizedType === '' || $normalizedType === 'current') {
        $descriptionText = strtolower((string)$tx_description);
        $looksLikeTransfer = strpos($descriptionText, 'transfer to ') !== false
            || strpos($descriptionText, 'wire transfer') !== false
            || strpos($descriptionText, 'bank transfer') !== false;
        $normalizedType = $looksLikeTransfer ? 'transfer' : (((float)$tx_amount < 0) ? 'withdrawal' : 'deposit');
    }
    $normalizedType = ucwords(str_replace(['_', '-'], ' ', $normalizedType));

    $normalizedStatus = strtolower(trim((string)$tx_status));
    if ($normalizedStatus === 'completed') {
        $normalizedStatus = 'successful';
    }
    if ($normalizedStatus === '' || $normalizedStatus === 'current') {
        $normalizedStatus = 'posted';
    }

    $rowCurrency = strtoupper((string)$tx_currency);
    if (!velmoraIsSupportedCurrency($rowCurrency)) {
        $rowCurrency = $currentAccountBalances[(string)$tx_account_number]['currency'] ?? 'USD';
    }

    $balanceBeforeRow = $currentAccountBalances[(string)$tx_account_number]['amount'] ?? 0.0;

    $dashboardRows[] = [
        'date' => date('M d, Y', (int)$tx_time),
        'description' => $tx_description,
        'category' => $normalizedType,
        'status' => ucwords(str_replace(['_', '-'], ' ', $normalizedStatus)),
        'amount' => (float)$tx_amount,
        'currency' => $rowCurrency,
        'counter_currency' => strtoupper((string)$tx_counter_currency),
        'counter_amount' => $tx_counter_amount !== null ? (float)$tx_counter_amount : null,
        'fx_rate' => $tx_fx_rate !== null ? (float)$tx_fx_rate : null,
        'balance' => $balanceBeforeRow,
    ];

    if ($normalizedStatus !== 'failed' && isset($currentAccountBalances[(string)$tx_account_number])) {
        $currentAccountBalances[(string)$tx_account_number]['amount'] -= (float)$tx_amount;
    }
}
$stmt->close();
$dbMetrics->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
    <title>Dashboard</title>

    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include('../common-sections/dashboard-header.html')?>
<?php if (!empty($is_demo_account)): ?>
<div style="background:#fff3cd;border-bottom:1px solid #f0d98a;color:#664d03;padding:10px 16px;text-align:center;font-weight:700;font-size:14px;">
    DEMO ACCOUNT — Simulated data for presentation and testing only
</div>
<?php endif; ?>
<section class="account-info reference-dashboard">
    <div class="container">
        <div class="cta-sec">
            <div class="left">
                <h2 class="greeting">Welcome back, <?php echo htmlspecialchars(explode(' ', $dashboardMeta['account_holder'])[0]); ?></h2>
                <p class="last-login">Here's what's happening with your account today.</p>
                <form method="get" class="display-currency-form" style="margin-top:10px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                    <label for="displayCurrency" style="font-size:12px;color:#667991;font-weight:700;">Display currency</label>
                    <select id="displayCurrency" name="display_currency" onchange="this.form.submit()" style="min-height:36px;border:1px solid #e2eaf4;border-radius:8px;padding:7px 10px;background:#fff;">
                        <?php echo velmoraCurrencyOptions($displayCurrency); ?>
                    </select>
                </form>
            </div>
            <div class="right profile-avatar-wrap">
                <?php if ($user_profile_picture && $user_profile_picture !== 'nil'): ?>
                    <img src="/dashboard/security/complete-kyc/uploads/<?php echo htmlspecialchars($user_profile_picture); ?>" alt="<?php echo htmlspecialchars($dashboardMeta['account_holder']); ?> profile picture" class="dashboard-avatar">
                <?php else: ?>
                    <img src="/assets/images/placeholder-image.png" alt="Default profile picture" class="dashboard-avatar">
                <?php endif; ?>
            </div>
        </div>

        <div class="bars">
            <div class="bar account hero-balance">
                <p class="title">Available Balance <span class="currency-chip"><?php echo htmlspecialchars($displayCurrency); ?> equivalent</span></p>
                <h1 class="figure"><?php echo htmlspecialchars(velmoraFormatCurrency($dashboardBalance, $displayCurrency)); ?></h1>
                <span class="month"><?php echo ($creditCount + $debitCount) . ' ' . (($creditCount + $debitCount) == 1 ? 'transaction' : 'transactions'); ?></span>
            </div>
            <div class="bar">
                <p class="title">Total Credits</p>
                <h1 class="figure text-green"><?php echo htmlspecialchars(velmoraFormatCurrency($totalCredits, $displayCurrency)); ?></h1>
                <span class="month"><?php echo $creditCount . ' ' . ($creditCount == 1 ? 'transaction' : 'transactions'); ?></span>            </div>
            <div class="bar">
                <p class="title">Total Debits</p>
                <h1 class="figure text-red"><?php echo htmlspecialchars(velmoraFormatCurrency($totalDebits, $displayCurrency)); ?></h1>
                <span class="month"><?php echo $debitCount . ' ' . ($debitCount == 1 ? 'transaction' : 'transactions'); ?></span>
            </div>
        </div>

        <div class="account-summary-panel">
            <h3>Account Information</h3>
            <div class="summary-grid">
                <div><span>Account Holder</span><strong><?php echo htmlspecialchars($dashboardMeta['account_holder']); ?></strong></div>
                <div><span>Date of Birth</span><strong><?php echo htmlspecialchars($dashboardMeta['date_of_birth']); ?></strong></div>
                <div><span>Account Number</span><strong><?php echo htmlspecialchars($dashboardMeta['account_number']); ?></strong></div>
                <div><span>Account Type</span><strong><?php echo htmlspecialchars($dashboardMeta['account_type']); ?></strong></div>
                <?php if (!empty($is_demo_account)): ?>
                <div><span>Profession</span><strong><?php echo htmlspecialchars($dashboardMeta['profession']); ?></strong></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<main class="reference-transactions">
    <div class="container">
        <div class="right full-width">
            <div class="transactions-toolbar">
                <h2>Recent Transactions</h2>
                <a href="accounts/transactions" class="sec-cta">View all</a>
            </div>
            <div class="accounts-list">
                <table>
                    <thead>
                    <tr>
                        <th>Date</th>
                        <th>Description</th>
                        <th>Category</th>
                        <th>Status</th>
                        <th>Amount</th>
                        <th>Balance</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($dashboardRows as $row):
                        $amount = (float)$row['amount'];
                        $isCredit = $amount >= 0;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['date']); ?></td>
                            <td><?php echo htmlspecialchars($row['description']); ?></td>
                            <td><span class="tx-category"><?php echo htmlspecialchars($row['category']); ?></span></td>
                            <td><span class="tx-category"><?php echo htmlspecialchars($row['status']); ?></span></td>
                            <td class="<?php echo $isCredit ? 'amount-credit' : 'amount-debit'; ?>">
                                <?php echo $isCredit ? '+' : '-'; ?><?php echo htmlspecialchars(velmoraFormatCurrency(abs($amount), $row['currency'])); ?>
                                <?php if (!empty($row['counter_currency']) && $row['counter_amount'] !== null && $row['counter_currency'] !== $row['currency']): ?>
                                    <small style="display:block;color:#667991;font-weight:500;margin-top:3px;">Countervalue: <?php echo htmlspecialchars(velmoraFormatCurrency((float)$row['counter_amount'], $row['counter_currency'])); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if (isset($row['balance'])) {
                                    echo htmlspecialchars(velmoraFormatCurrency((float)$row['balance'], $row['currency']));
                                } else {
                                    echo '-';
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
