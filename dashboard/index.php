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
];

$dbMetrics = connectToDatabase();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND status = 'Pending'");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($pending_transactions);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND status = 'Successful'");
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
    if (!empty($kycFullName)) {
        $dashboardMeta['account_holder'] = $kycFullName;
    }
}
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT account_number, account_type FROM accounts WHERE user_email = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($meta_account_number, $meta_account_type);
if ($stmt->fetch()) {
    $dashboardMeta['account_number'] = '****' . substr((string)$meta_account_number, -4);
    $dashboardMeta['account_type'] = $meta_account_type;
}
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT type, description, amount, status, `time` FROM transactions WHERE user_email = ? ORDER BY `time` DESC LIMIT 10");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($tx_type, $tx_description, $tx_amount, $tx_status, $tx_time);
while ($stmt->fetch()) {
    $normalizedType = strtolower(trim((string)$tx_type));
    if ($normalizedType === '' || $normalizedType === 'current') {
        $normalizedType = ((float)$tx_amount < 0) ? 'withdrawal' : 'deposit';
    }
    $normalizedType = ucwords(str_replace(['_', '-'], ' ', $normalizedType));
    $dashboardRows[] = [
        'date' => date('M d, Y', (int)$tx_time),
        'description' => $tx_description,
        'category' => $normalizedType,
        'status' => $tx_status ?: 'Posted',
        'amount' => (float)$tx_amount,
    ];
}
$stmt->close();
$dbMetrics->close();

$summaryDb = connectToDatabase();
$summaryStmt = $summaryDb->prepare("
    SELECT
        COALESCE(SUM(CASE WHEN amount >= 0 THEN amount ELSE 0 END), 0) AS total_credits,
        COALESCE(SUM(CASE WHEN amount < 0 THEN ABS(amount) ELSE 0 END), 0) AS total_debits,
        COALESCE(SUM(CASE WHEN amount >= 0 THEN 1 ELSE 0 END), 0) AS credit_count,
        COALESCE(SUM(CASE WHEN amount < 0 THEN 1 ELSE 0 END), 0) AS debit_count
    FROM transactions
    WHERE user_email = ?
");
$summaryStmt->bind_param('s', $user_email);
$summaryStmt->execute();
$summaryStmt->bind_result($totalCredits, $totalDebits, $creditCount, $debitCount);
$summaryStmt->fetch();
$summaryStmt->close();
$summaryDb->close();

$runningBalance = (float)str_replace(',', '', $user_balance);
foreach ($dashboardRows as &$row) {
    $row['balance'] = $runningBalance;
    $runningBalance -= (float)$row['amount'];
}
unset($row);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <title>Dashboard</title>

    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include('../common-sections/dashboard-header.html')?>
<section class="account-info reference-dashboard">
    <div class="container">
        <div class="cta-sec">
            <div class="left">
                <h2 class="greeting">Welcome back, <?php echo htmlspecialchars(explode(' ', $dashboardMeta['account_holder'])[0]); ?></h2>
                <p class="last-login">Here's what's happening with your account today.</p>
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
                <p class="title">Available Balance</p>
                <h1 class="figure">$<?php echo htmlspecialchars($user_balance); ?></h1>
                <span class="month">Based on <?php echo (int)$transaction_count; ?> transactions</span>
            </div>
            <div class="bar">
                <p class="title">Total Credits</p>
                <h1 class="figure text-green">$<?php echo number_format($totalCredits, 2); ?></h1>
                <span class="month"><?php echo $creditCount; ?> transactions</span>
            </div>
            <div class="bar">
                <p class="title">Total Debits</p>
                <h1 class="figure text-red">$<?php echo number_format($totalDebits, 2); ?></h1>
                <span class="month"><?php echo $debitCount; ?> transactions</span>
            </div>
        </div>

        <div class="account-summary-panel">
            <h3>Account Information</h3>
            <div class="summary-grid">
                <div><span>Account Holder</span><strong><?php echo htmlspecialchars($dashboardMeta['account_holder']); ?></strong></div>
                <div><span>Date of Birth</span><strong><?php echo htmlspecialchars($dashboardMeta['date_of_birth']); ?></strong></div>
                <div><span>Account Number</span><strong><?php echo htmlspecialchars($dashboardMeta['account_number']); ?></strong></div>
                <div><span>Account Type</span><strong><?php echo htmlspecialchars($dashboardMeta['account_type']); ?></strong></div>
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
                            <td class="<?php echo $isCredit ? 'amount-credit' : 'amount-debit'; ?>">
                                <?php echo $isCredit ? '+' : '-'; ?>$<?php echo number_format(abs($amount), 2); ?>
                            </td>
                            <td>
                                <?php
                                if (isset($row['balance'])) {
                                    echo '$' . number_format((float)$row['balance'], 2);
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
