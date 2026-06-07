<?php include('../app.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Control Panel — All Accounts</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
</head>
<body>
<?php include('../../common-sections/control-panel-header.php'); ?>
<section class="table site-users" style="padding: 100px 0;">
    <div class="container">
        <h2>User Accounts Full List</h2>
        <?php
            $db = connectToDatabase();
            $query = "SELECT id, user_name, user_email, account_number, account_status, creation_time FROM accounts ORDER BY creation_time DESC";
            $result = $db->query($query);

            $accounts = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $accounts[] = $row;
                }
            }

            $balances = [];
            if (!empty($accounts)) {
                $acctNums = implode(',', array_map(fn($a) => "'" . $db->real_escape_string($a['account_number']) . "'", $accounts));
                $balResult = $db->query("SELECT account_number, SUM(amount) AS account_balance FROM transactions WHERE account_number IN ($acctNums) AND status IN ('Successful','Pending') GROUP BY account_number");
                while ($brow = $balResult->fetch_assoc()) {
                    $balances[$brow['account_number']] = $brow['account_balance'];
                }
            }
        ?>

        <table>
            <thead>
                <tr>
                    <td>ID</td>
                    <td>User Name</td>
                    <td>User Email</td>
                    <td>Account Number</td>
                    <td>Balance</td>
                    <td>Account Status</td>
                    <td>Date</td>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($accounts)): ?>
                    <?php foreach ($accounts as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                            <td>$<?php echo number_format((float)($balances[$row['account_number']] ?? 0), 2); ?></td>
                            <td><?php echo htmlspecialchars($row['account_status']); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', (int)$row['creation_time'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No accounts found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php $db->close(); ?>
    </div>
</section>
<script src="/assets/scripts/control-panel.js?v=<?php echo time(); ?>"></script>
</body>
</html>
