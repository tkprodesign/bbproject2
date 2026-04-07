<?php include('app.php') ?>
<?php
$pending_transactions = 0;
$successful_transactions = 0;
$latest_transaction_time = null;
$active_accounts = 0;
$kyc_status_label = 'Not Submitted';

$dbMetrics = connectToDatabase();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND status = 'Pending'");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($pending_transactions);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND status = 'Successful'");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($successful_transactions);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT MAX(`time`) FROM transactions WHERE user_email = ?");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($latest_transaction_time);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT COUNT(*) FROM accounts WHERE user_email = ? AND account_status = 'Active'");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($active_accounts);
$stmt->fetch();
$stmt->close();

$stmt = $dbMetrics->prepare("SELECT status FROM kyc_data WHERE email = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($latest_kyc_status);
if ($stmt->fetch() && !empty($latest_kyc_status)) {
    $kyc_status_label = $latest_kyc_status;
}
$stmt->close();
$dbMetrics->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <title>Dashboard</title>
    
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
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

        <div class="insights-grid">
            <article class="insight-card">
                <p class="kicker">Transactions</p>
                <h3><?php echo (int)$successful_transactions; ?></h3>
                <p>Successful transactions completed.</p>
                <a href="/dashboard/accounts/transactions">View history</a>
            </article>
            <article class="insight-card">
                <p class="kicker">Pending</p>
                <h3><?php echo (int)$pending_transactions; ?></h3>
                <p>Transactions currently being processed.</p>
                <a href="/dashboard/fund/transfer">Make transfer</a>
            </article>
            <article class="insight-card">
                <p class="kicker">Active accounts</p>
                <h3><?php echo (int)$active_accounts; ?></h3>
                <p>Active wallets available for use.</p>
                <a href="/dashboard/accounts">Manage accounts</a>
            </article>
            <article class="insight-card">
                <p class="kicker">KYC status</p>
                <h3><?php echo htmlspecialchars($kyc_status_label); ?></h3>
                <p>
                    <?php if ($latest_transaction_time): ?>
                        Last transaction: <?php echo date('d M Y H:i', $latest_transaction_time); ?>
                    <?php else: ?>
                        No transaction record yet.
                    <?php endif; ?>
                </p>
                <a href="/dashboard/security/complete-kyc">Update identity info</a>
            </article>
        </div>

        <div class="quick-actions">
            <a href="/dashboard/fund/transfer" class="quick-action">Transfer funds</a>
            <a href="/dashboard/accounts/create" class="quick-action">Create account wallet</a>
            <a href="/dashboard/security/change-password" class="quick-action">Change password</a>
            <a href="/dashboard/security/preferences" class="quick-action">Security preferences</a>
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
