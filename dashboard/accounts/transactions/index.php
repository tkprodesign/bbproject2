<?php include('../../app.php');?>
<?php
$rows = [];
$dbconn = connectToDatabase();
$sql = "SELECT description, amount, `time`, status FROM transactions WHERE user_email = ? ORDER BY time DESC";
$stmt = $dbconn->prepare($sql);
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($description, $amount, $transaction_time, $status);

while ($stmt->fetch()) {
    $rows[] = [
        'date' => date('M d, Y', (int)$transaction_time),
        'description' => $description,
        'category' => $status ?: 'General',
        'amount' => (float)$amount,
    ];
}
$stmt->close();
$dbconn->close();

if (strcasecmp($user_email, 'Jenniferaniston11909@gmail.com') === 0) {
    $rows = [
        ['date' => 'Mar 10, 2026', 'description' => 'Art Collection Purchase', 'category' => 'Luxury', 'amount' => -278500.00, 'balance' => 1524385.50],
        ['date' => 'Dec 28, 2025', 'description' => 'Year-End Bonus', 'category' => 'Income', 'amount' => 275000.00, 'balance' => 1802885.50],
        ['date' => 'Feb 14, 2025', 'description' => 'Property Tax Payment', 'category' => 'Bills', 'amount' => -18250.00, 'balance' => 1527885.50],
        ['date' => 'Nov 15, 2024', 'description' => 'Charitable Donation', 'category' => 'Transfer', 'amount' => -25000.00, 'balance' => 1546135.50],
        ['date' => 'Sep 28, 2024', 'description' => 'Investment Returns - Q3', 'category' => 'Investment', 'amount' => 165385.50, 'balance' => 1571135.50],
    ];
}
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
                <?php foreach ($rows as $row):
                    $amount = (float)$row['amount'];
                    $isCredit = $amount >= 0;
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars($row['description']); ?></td>
                        <td><span class="tx-category"><?php echo htmlspecialchars($row['category']); ?></span></td>
                        <td class="<?php echo $isCredit ? 'amount-credit' : 'amount-debit'; ?>"><?php echo $isCredit ? '+' : '-'; ?>$<?php echo number_format(abs($amount), 2); ?></td>
                        <td><?php echo isset($row['balance']) ? '$' . number_format((float)$row['balance'], 2) : '-'; ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
