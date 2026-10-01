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
            $query = "SELECT id, name, email, kyc_level, date_registered FROM users ORDER BY date_registered DESC";
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
                $balResult = $db->query("SELECT user_email, SUM(amount) AS user_balance FROM transactions WHERE user_email IN ($emailList) AND status IN ('Successful','Pending') GROUP BY user_email");
                while ($brow = $balResult->fetch_assoc()) {
                    $balances[$brow['user_email']] = $brow['user_balance'];
                }
            }
        ?>

        <table>
            <thead>
                <tr>
                    <td>User ID</td>
                    <td>Name</td>
                    <td>Email</td>
                    <td>Balance</td>
                    <td>KYC Level</td>
                    <td>Date Registered</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($users)): ?>
                    <?php foreach ($users as $row): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>$<?php echo number_format((float)($balances[$row['email']] ?? 0), 2); ?></td>
                            <td><?php echo htmlspecialchars($row['kyc_level']); ?></td>
                            <td><?php echo htmlspecialchars(date('d M Y', (int)$row['date_registered'])); ?></td>
                            <td><a href="/control-panel/profile-picture/?id=<?php echo htmlspecialchars($row['id']); ?>">View Profile</a></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">No users found</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php $db->close(); ?>
    </div>
</section>
<script src="/assets/scripts/control-panel.js?v=<?php echo time(); ?>"></script>
</body>
</html>
