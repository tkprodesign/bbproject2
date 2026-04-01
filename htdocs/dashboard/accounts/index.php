<?php include('../app.php')?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/images/branding/icon.png">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
    <!-- Smartsupp Live Chat script -->
<script type="text/javascript">
var _smartsupp = _smartsupp || {};
_smartsupp.key = '598556b50d3bd3f128d2c9362dccbf940458225d';
window.smartsupp||(function(d) {
  var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
  s=d.getElementsByTagName('script')[0];c=d.createElement('script');
  c.type='text/javascript';c.charset='utf-8';c.async=true;
  c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
})(document);
</script>
<noscript> Powered by <a href=“https://www.smartsupp.com” target=“_blank”>Smartsupp</a></noscript>
</head>
<body>
<?php include('../../common-sections/dashboard-header.html')?>
<section class="add-account">
    <div class="container">
        <a href="create" class="manage-accounts"><span class="material-symbols-outlined">
            add
            </span>Add New Account
        </a>
        <div class="accounts-list">
            <?php
                $dbconn = connectToDatabase();
                $sql = "SELECT account_type, account_number, currency, account_status FROM accounts WHERE user_email = ?";
                $stmt = $dbconn->prepare($sql);
                // Check if the statement was prepared successfully
                if ($stmt === false) {
                    die("Error preparing statement: " . $dbconn->error);
                }
                $stmt->bind_param('s', $user_email);
                $stmt->execute();
                $stmt->bind_result($account_type, $account_number, $currency, $status);

                $accounts = [];
                while ($stmt->fetch()) {
                    $accounts[] = [
                        'account_type' => $account_type,
                        'account_number' => $account_number,
                        'currency' => $currency,
                        'status' => $status
                    ];
                }

                $stmt->close();

                function getAccountBalance($dbconn, $account_number) {
                    $sql = "SELECT SUM(amount) FROM transactions WHERE account_number = ?";
                    $stmt = $dbconn->prepare($sql);
                    if ($stmt === false) {
                        die("Error preparing statement: " . $dbconn->error);
                    }
                    $stmt->bind_param('s', $account_number);
                    $stmt->execute();
                    $stmt->bind_result($balance);
                    $stmt->fetch();
                    $stmt->close();
                    return $balance ? number_format($balance, 2) : '0.00';
                }
            ?>

                <table>
                    <thead>
                        <tr>
                            <th>Account Type</th>
                            <th>Account Number</th>
                            <th>Account Currency</th>
                            <th>Available Balance</th>
                            <th>Account Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($accounts as $account): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($account['account_type']); ?></td>
                                <td><?php echo htmlspecialchars($account['account_number']); ?></td>
                                <td><?php echo htmlspecialchars($account['currency']); ?></td>
                                <td><?php echo htmlspecialchars(getAccountBalance($dbconn, $account['account_number'])); ?></td>
                                <td><?php echo htmlspecialchars($account['status']); ?></td>
                                <!-- <td><a href="/dashboard/accounts/manage?account-number=<?php echo htmlspecialchars($account['account_number']); ?>">Perform Action</a></td> -->
                                <td><a href="manage?nos=<?php echo $account['account_number']; ?>s">Perform Action</a></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

            <?php
                $dbconn->close();
            ?>
        </div>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>