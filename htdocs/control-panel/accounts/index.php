<?php include('../app.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Control Panel</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 1000px)">
</head>
<body>
<section class="table site-users" style="padding: 100px 0;">
    <div class="container">
        <h2>User Accounts Full List</h2>
        <?php
            $db = connectToDatabase();
            $query = "SELECT * FROM accounts ORDER BY `time` DESC";
            $result = $db->query($query);
        ?>

        <table>
            <thead>
                <tr>
                    <td>User ID</td>
                    <td>User Name</td>
                    <td>User Email</td>
                    <td>Account Number</td>
                    <td>Balance</td>
                    <td>Account Status</td>
                    <td>Date</td>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                            <?php 
                                $select_user_account = $row['account_number'];
                                $su_query = "SELECT SUM(amount) AS account_balance FROM transactions WHERE account_number = '$select_user_account'";
                                $su_result = $db->query($su_query);
                                $account_balance = $su_result->fetch_assoc()['account_balance'] ?? 0;
                                ?>
                            <td>$<?php echo htmlspecialchars(number_format($account_balance, 2)); ?></td>
                            <td><?php echo htmlspecialchars($row['status']); ?></td>
                            <td><?php echo htmlspecialchars(date('d, F Y', $row['creation_time'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No accounts found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $db->close();
        ?>

        <a href="/control-panel/accounts" class="cta">View all accounts</a>
    </div>
</section>
</body>
</html>