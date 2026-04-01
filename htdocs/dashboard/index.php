<?php include('app.php') ?>
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
<?php include('../common-sections/dashboard-header.html')?>
<section class="account-info">
    <div class="container">
        <?php if($accounts_count == 0):?>
            <div class="not-sec">
                <p>You have not created any currency accounts, you must have at least 1 account to receive funds and send funds.</p>
                <a href="/dashboard/accounts/create">Create Fund Account</a>
            </div>
        <?php endif;?>
        <div class="cta-sec">
            <div class="left">
                <h2 class="greeting">Hi, <?php echo $user_name; ?></h2>
                <p class="last-login">Last login was <?php echo date('d, F Y', $user_last_active); ?></p>
            </div>
            <div class="right">
                <a href="/dashboard/accounts" class="sec-cta">View account</a>
                <a href="/dashboard/accounts/create" class="pri-cta">
                    <span>Add account</span>
                    <span class="material-symbols-outlined icon">
                        add
                        </span>
                </a>
            </div>
        </div>
        <div class="bars">
            <div class="bar account">
                <p class="name"><?php echo $user_name; ?></p>
                <p class="account-type">Account type: 
                    <?php
                        if($accounts_count == 0){
                            echo 'Single';
                        } else {
                            echo 'Multiple';
                        }
                    ?>
                </p>
            </div>
            <div class="bar balance">
                <h1 class="figure">$<?php echo $user_balance; ?></h1>
                <p class="title">Current Balance</p>
                <span class="month">
                <?php
                    $current_time = date('F, Y');
                    echo '<span class="month">As of ' . $current_time . '</span>';
                ?>
                </span>
            </div>
            <div class="bar accounts-counter">
                <h1 class="figure"><?php echo $accounts_count; ?></h1>
                <p class="title">Total Accounts</p>
            </div>
        </div>
    </div>
</section>
<main>
    <div class="container">
        <div class="left">
            <h1>Picture Identification</h1>
            <?php if($user_profile_picture == 'nil'): ?>
                <img src="/assets/images/placeholder-image.png" alt="Pofile Picture" class="profile-picture">
            <?php else: ?>
                <img src="/dashboard/security/complete-kyc/uploads/<?php echo $user_profile_picture;?>" alt="Pofile Picture" class="profile-picture">
            <?php endif; ?>
            <a href="security/complete-kyc" class="upload">Upload Identification</a>
            <p class="notification">Note:<br>
                Identification image must meet the banking system standard of KYC image policy.
                All identification image will undergo the strong human verification act.</p>
        </div>
        <div class="right">
            <h2>Recent Transactions</h2>
            <table>
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Desc</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                    $dbconn = connectToDatabase();
                    $sql = "SELECT type, amount, status, description, `time` FROM transactions WHERE user_email = ? ORDER BY `time` DESC LIMIT 10";
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
            <a href="accounts/transactions">View all transactions</a>
        </div>
    </div>
</main>
</div>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>