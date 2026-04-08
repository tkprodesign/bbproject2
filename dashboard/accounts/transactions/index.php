<?php include('../../app.php');?>
<?php
$rows = [];
$dbconn = connectToDatabase();
$sql = "SELECT type, description, amount, status, `time` FROM transactions WHERE user_email = ? ORDER BY time DESC";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($type, $description, $amount, $status, $transaction_time);

while ($stmt->fetch()) {
    $rows[] = [
        'date' => date('M d, Y', (int)$transaction_time),
        'description' => $description,
        'status' => $status ?: ($amount < 0 ? 'Debit' : 'Credit'),
        'category' => $type ?: ($amount < 0 ? 'Withdrawal' : 'Deposit'),
        'amount' => (float)$amount,
    ];
}
$stmt->close();
$dbconn->close();

$runningBalance = (float)str_replace(',', '', $user_balance);
foreach ($rows as &$row) {
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
                        <td class="<?php echo $isCredit ? 'amount-credit' : 'amount-debit'; ?>"><?php echo $isCredit ? '+' : '-'; ?>$<?php echo number_format(abs($amount), 2); ?></td>
                        <td><?php echo isset($row['balance']) ? '$' . number_format((float)$row['balance'], 2) : '-'; ?></td>
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
