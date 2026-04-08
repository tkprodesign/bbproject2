<?php include('../../app.php');?>
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
<section class="transactions">
    <div class="container">
        <a href="#" class="manage-accounts"><span class="material-symbols-outlined">
            add
            </span>Add New Account
        </a>
        <div class="accounts-list">
            <table>
                <thead> 
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Description</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                        $dbconn = connectToDatabase();
                        $sql = "SELECT type, amount, status, description, `time` FROM transactions WHERE user_email = ? ORDER BY time DESC";
                        $stmt = $dbconn->prepare($sql);
                        $stmt->bind_param('s', $user_email);
                        $stmt->execute();
                        $stmt->bind_result($type, $amount, $status, $description, $transaction_time);

                        while ($stmt->fetch()) {
                            $transaction_date = $transaction_time;
                            $formatted_date = date("d, F Y", $transaction_date);
                            $formatted_time = date("H:i \E\T", $transaction_time);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($type); ?></td>
                                <td>$<?php echo htmlspecialchars(number_format(abs($amount), 2)); ?></td>
                                <td class="<?php echo strtolower(htmlspecialchars($status)); ?>"><?php echo htmlspecialchars($status); ?></td>
                                <td><?php echo htmlspecialchars($description); ?></td>
                                <td><?php echo htmlspecialchars($formatted_date); ?></td>
                                <td><?php echo htmlspecialchars($formatted_time); ?></td>
                            </tr>
                    <?php
                        }
                        $stmt->close();
                        $dbconn->close();
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>