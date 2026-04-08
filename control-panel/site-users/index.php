<?php include('../app.php') ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Control Panel</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 720px)">
</head>
<body>
<section class="table site-users" style="padding: 100px 0;">
    <div class="container">
        <h2>Site Users Full List</h2>
        <?php
            $db = connectToDatabase();
            $query = "SELECT * FROM users ORDER BY `date_registered` DESC";
            $result = $db->query($query);
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
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <?php 
                                $select_user_email = $row['email'];
                                $su_query = "SELECT SUM(amount) AS user_balance FROM transactions WHERE user_email = '$select_user_email' AND status IN ('Successful', 'Pending')";
                                $su_result = $db->query($su_query);
                                $user_balance = $su_result->fetch_assoc()['user_balance'] ?? 0;
                            ?>
                            <td>$<?php echo htmlspecialchars(number_format($user_balance, 2)); ?></td>
                            <td><?php echo htmlspecialchars($row['kyc_level']); ?></td>
                            <td><?php echo htmlspecialchars(date('d, F Y', $row['date_registered'])); ?></td>
                            <td><a href="/control-panel/profile-picture/?id=<?php echo htmlspecialchars($row['id']); ?>">View Profile Picture</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $db->close();
        ?>

    </div>
</section>
</body>
</html>