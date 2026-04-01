<?php include('../../app.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="shortcut icon" href="/assets/images/brand/favicon.png" type="image/png">
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html')?>
<section class="add-account">
    <div class="container">
        <a href="#" class="manage-accounts">Manage Accounts</a>
        <form action="" method="post">
            <h2>Create New Account Wallet</h2>
            <div class="input-box">
                <label>Account Name</label>
                <input type="text" name="user_name" readonly value="<?php echo $user_name; ?>" class="dark-bg">
            </div>
            <div class="input-box">
                <label>Currency Type</label>
                <select name="currency">
                    <option value disabled selected>Choose</option>
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="input-box">
                <label>Account Type</label>
                <select name="account_type">
                    <option value disabled selected>Choose</option>
                    <option value="Savings">Savings</option>
                    <option value="Current">Current</option>
                    <option value="Fixed">Fixed</option>
                </select>
            </div>
            <div class="input-box">
                <button type="submit" name="create_account" value="create-account">Create Account</button>
            </div>
        </form>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>