<?php include('../app.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Control Panel - Transactions</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
</head>
<body>
<?php include('../../common-sections/control-panel-header.php'); ?>
    <section class="table list-of-withdrawals" style="padding: 100px 0;">
        <div class="container">
            <h2>List of Transactions</h2>
            <?php
                // Database connection
                $db = connectToDatabase();
    
                // Query to get data from transactions table
                $query = "SELECT * FROM transactions ORDER BY time DESC";
                $result = $db->query($query);
                ?>
    
                <table>
                    <thead>
                        <tr>
                            <td>ID</td>
                            <td>Transaction Type</td>
                            <td>Reference</td>
                            <td>Email</td>
                            <td>Account Number</td>
                            <td>Amount</td>
                            <td>Channel</td>
                            <td>Status</td>
                            <td>Value Date</td>
                            <td>Posted</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['transaction_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                                    <td>
                                    <?php echo htmlspecialchars(velmoraFormatCurrency(abs((float)$row['amount']), velmoraIsSupportedCurrency((string)$row['currency']) ? strtoupper((string)$row['currency']) : 'USD')); ?>
                                    <?php if (!empty($row['counter_currency']) && $row['counter_amount'] !== null && strtoupper((string)$row['counter_currency']) !== strtoupper((string)$row['currency'])): ?>
                                        <small style="display:block;color:#667991;">Countervalue: <?php echo htmlspecialchars(velmoraFormatCurrency((float)$row['counter_amount'], strtoupper((string)$row['counter_currency']))); ?></small>
                                        <?php if (!empty($row['fx_rate'])): ?><small style="display:block;color:#7a8ba0;">Rate <?php echo number_format((float)$row['fx_rate'], 6); ?><?php if ($row['fx_spread_bps'] !== null): ?> · Margin <?php echo number_format(((int)$row['fx_spread_bps']) / 100, 2); ?>%<?php endif; ?></small><?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                    <td><?php echo htmlspecialchars(!empty($row['channel']) ? $row['channel'] : 'Online Banking'); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo !empty($row['value_date']) ? htmlspecialchars(date('d M Y', strtotime((string)$row['value_date']))) : htmlspecialchars(date('d M Y', (int)$row['time'])); ?></td>
                                    <td><?php echo !empty($row['posted_at']) ? htmlspecialchars(date('d M Y H:i', strtotime((string)$row['posted_at']))) : htmlspecialchars(date('d M Y H:i', (int)$row['time'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="11">No transactions found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
    
                <?php
                // Close the database connection
                $db->close();
            ?>
    
        </div>
    </section>
</body>
</html>