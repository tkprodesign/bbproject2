<?php include('../app.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Control Panel - Transactions</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time();?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/control-panel.css?v=<?php echo time();?>" media="screen and (max-width: 720px)">
</head>
<body>
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
                            <td>Email</td>
                            <td>Account Number</td>
                            <td>Amount</td>
                            <td>Status</td>
                            <td>Time</td>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['type']); ?></td>
                                    <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                                    <td><?php echo htmlspecialchars(abs($row['amount'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d, F Y H:i:s /E/T', $row['time'])); ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7">No transactions found</td>
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