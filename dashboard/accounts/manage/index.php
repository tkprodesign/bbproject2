<?php include('../../app.php');?>
<?php
$account_number = null;
if (!isset($_GET['nos'])) {
    header('Location: /dashboard/accounts');
    exit();
}

$nos = $_GET['nos'];
$account_number = (int)substr($nos, 0, -1);
if ($account_number <= 0) {
    header('Location: /dashboard/accounts');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Manage Account</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html')?>
<section class="manage-account">
    <div class="container dashboard-card">
        <div class="heading">
            <p>Perform actions on account <b><?php echo htmlspecialchars($account_number); ?></b></p>
        </div>
        <div class="content modern-grid">
            <div class="row row-1">
                <img src="/assets/images/dashboard/blocked.png" alt="Block account">
                <div>
                    <h3>Request Account Block</h3>
                    <p>Need to secure your account quickly? Contact support to request a temporary block.</p>
                    <a href="/contact/">Contact support</a>
                </div>
            </div>
            <div class="row row-2">
                <img src="/assets/images/dashboard/currencies.jpg" alt="Change currency">
                <div>
                    <h3>Change Currency</h3>
                    <p>Open a new wallet with the currency you need from the accounts page.</p>
                    <a href="/dashboard/accounts/create">Add currency wallet</a>
                </div>
            </div>
        </div>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
