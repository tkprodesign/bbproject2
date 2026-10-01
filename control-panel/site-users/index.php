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
    <title>Control Panel — All Users</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
</head>
<body>
<?php include('../../common-sections/control-panel-header.php'); ?>
<section class="table site-users" style="padding: 100px 0;">
    <div class="container">
        <h2>Site Users Full List</h2>
        <?php
            $db = connectToDatabase();
            $query = "SELECT id, customer_number, name, email, user_status, kyc_level, date_registered, last_login_at FROM users ORDER BY date_registered DESC";
            $result = $db->query($query);

            $users = [];
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $users[] = $row;
                }
            }

            $balances = [];
            if (!empty($users)) {
                $emailList = implode(',', array_map(fn($u) => "'" . $db->real_escape_string($u['email']) . "'", $users));
                $balResult = $db->query("SELECT user_email, currency, SUM(amount) AS user_balance FROM transactions WHERE user_email IN ($emailList) AND status IN ('Successful','Pending') GROUP BY user_email, currency");
                while ($brow = $balResult->fetch_assoc()) {
                    $balances[$brow['user_email']][] = [
                        'currency' => strtoupper((string)$brow['currency']),
                        'amount' => (float)$brow['user_balance'],
                    ];
                }
            }
        ?>

        <table>
            <thead>
                <tr>
                    <td>User ID</td>
                    <td>Customer No.</td>
                    <td>Name</td>
                    <td>Email</td>
                    <td>Balances</td>
                    <td>Status</td>
                    <td>KYC</td>
                    <td>Registered</td>
                    <td>Last Login</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['customer_number'] ?: '—'); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php if (!empty($balances[$row['email']])): ?>
                                    <?php foreach ($balances[$row['email']] as $balance): ?>
                                        <div><?php echo htmlspecialchars(velmoraFormatCurrency($balance['amount'], velmoraIsSupportedCurrency($balance['currency']) ? $balance['currency'] : 'USD')); ?></div>
                                    <?php endforeach; ?>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($row['user_status'] ?: 'Active'); ?></td>
                            <td><?php echo htmlspecialchars($row['kyc_level']); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', (int)$row['date_registered'])); ?></td>
                            <td><?php echo !empty($row['last_login_at']) ? htmlspecialchars(date('d M Y H:i', strtotime((string)$row['last_login_at']))) : '—'; ?></td>
                            <td><a href="/control-panel/profile-picture/?id=<?php echo htmlspecialchars($row['id']); ?>">View Profile</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="10">No users found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php $db->close(); ?>
    </div>
</section>
<script src="/assets/scripts/control-panel.js?v=<?php echo time(); ?>"></script>
</body>
</html>
