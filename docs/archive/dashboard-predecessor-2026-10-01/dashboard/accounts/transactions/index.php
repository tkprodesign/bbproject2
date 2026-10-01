<?php include('../../app.php');?>
<?php
$rows = [];
$dbconn = connectToDatabase();

$currentBalances = [];
$balanceStmt = $dbconn->prepare("SELECT account_number, COALESCE(SUM(CASE WHEN status IS NULL OR LOWER(status) <> 'failed' THEN amount ELSE 0 END), 0) AS balance FROM transactions WHERE user_email = ? GROUP BY account_number");
$balanceStmt->bind_param('s', $user_email);
$balanceStmt->execute();
$balanceResult = $balanceStmt->get_result();
while ($balanceRow = $balanceResult->fetch_assoc()) {
    $currentBalances[(string)$balanceRow['account_number']] = (float)$balanceRow['balance'];
}
$balanceStmt->close();

$sql = "SELECT account_number, type, description, amount, currency, counter_currency, counter_amount, fx_rate, fx_spread_bps, status, `time` FROM transactions WHERE user_email = ? ORDER BY time DESC";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($accountNumber, $type, $description, $amount, $currency, $counterCurrency, $counterAmount, $fxRate, $fxSpreadBps, $status, $transaction_time);

while ($stmt->fetch()) {
    $normalizedStatus = strtolower(trim((string)$status));
    if ($normalizedStatus === '' || $normalizedStatus === 'current') {
        $normalizedStatus = 'posted';
    } elseif ($normalizedStatus === 'completed') {
        $normalizedStatus = 'successful';
    }
    $normalizedStatusLabel = ucwords(str_replace(['_', '-'], ' ', $normalizedStatus));

    $normalizedType = strtolower(trim((string)$type));
    if ($normalizedType === '' || $normalizedType === 'current') {
        $descriptionText = strtolower((string)$description);
        $looksLikeTransfer = strpos($descriptionText, 'transfer to ') !== false
            || strpos($descriptionText, 'wire transfer') !== false
            || strpos($descriptionText, 'bank transfer') !== false;
        $normalizedType = $looksLikeTransfer ? 'transfer' : (((float)$amount < 0) ? 'withdrawal' : 'deposit');
    }
    $normalizedType = ucwords(str_replace(['_', '-'], ' ', $normalizedType));

    $rowCurrency = strtoupper((string)$currency);
    if (!velmoraIsSupportedCurrency($rowCurrency)) {
        $rowCurrency = 'USD';
    }

    $rows[] = [
        'date' => date('M d, Y', (int)$transaction_time),
        'description' => $description,
        'status' => $normalizedStatusLabel,
        'status_key' => $normalizedStatus,
        'category' => $normalizedType,
        'amount' => (float)$amount,
        'currency' => $rowCurrency,
        'counter_currency' => strtoupper((string)$counterCurrency),
        'counter_amount' => $counterAmount !== null ? (float)$counterAmount : null,
        'fx_rate' => $fxRate !== null ? (float)$fxRate : null,
        'fx_spread_bps' => $fxSpreadBps !== null ? (int)$fxSpreadBps : null,
        'account_number' => (string)$accountNumber,
        'balance' => $currentBalances[(string)$accountNumber] ?? 0.0,
    ];

    if ($normalizedStatus !== 'failed' && isset($currentBalances[(string)$accountNumber])) {
        $currentBalances[(string)$accountNumber] -= (float)$amount;
    }
}
$stmt->close();
$dbconn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html')?>
<section class="transactions reference-transactions-page">
    <div class="container">
        <div class="transactions-toolbar advanced">
            <h2>Recent Transactions</h2>
            <div class="tx-actions">
                <input type="search" id="txSearchInput" placeholder="Search transactions..." aria-label="Search transactions">
                <button type="button" class="tx-filter active" data-filter="all">All</button>
                <button type="button" class="tx-filter" data-filter="credit">Credits</button>
                <button type="button" class="tx-filter" data-filter="debit">Debits</button>
                <button type="button" id="txExportBtn" class="sec-cta">Export</button>
            </div>
        </div>
        <div class="accounts-list">
            <table id="transactionsTable">
                <thead>
                <tr>
                    <th>Date</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Amount</th>
                    <th>Balance</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($rows as $row):
                    $amount = (float)$row['amount'];
                    $isCredit = $amount >= 0;
                    ?>
                    <tr data-tx-type="<?php echo $isCredit ? 'credit' : 'debit'; ?>">
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><span class="tx-category"><?php echo htmlspecialchars($row['status']); ?></span></td>
                        <td class="<?php echo $isCredit ? 'amount-credit' : 'amount-debit'; ?>"><?php echo $isCredit ? '+' : '-'; ?><?php echo htmlspecialchars(velmoraFormatCurrency(abs($amount), $row['currency'])); ?>
                            <?php if (!empty($row['counter_currency']) && $row['counter_amount'] !== null && $row['counter_currency'] !== $row['currency']): ?>
                                <small style="display:block;color:#667991;font-weight:500;margin-top:3px;">Countervalue: <?php echo htmlspecialchars(velmoraFormatCurrency((float)$row['counter_amount'], $row['counter_currency'])); ?></small>
                                <?php if (!empty($row['fx_rate'])): ?>
                                    <small style="display:block;color:#7a8ba0;font-weight:500;">Rate: <?php echo number_format((float)$row['fx_rate'], 6); ?><?php if ($row['fx_spread_bps'] !== null): ?> · Margin <?php echo number_format($row['fx_spread_bps'] / 100, 2); ?>%<?php endif; ?></small>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td><?php echo isset($row['balance']) ? htmlspecialchars(velmoraFormatCurrency((float)$row['balance'], $row['currency'])) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            <p id="txEmptyState" class="tx-empty-state" style="display:none;">No transactions match your current filters.</p>
        </div>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
