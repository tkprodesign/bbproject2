<?php include('../app.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/assets/images/branding/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/icon.png">

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
<?php include('../../common-sections/dashboard-header.html')?>
<section class="profile-kyc">
    <div class="container">
        <div class="heading">
            <h1>Profile & Verification</h1>
        </div>
        <div class="content">
            <a href="/dashboard/profile-picture" class="sec-cta">Update Profile Picture</a>
            <a href="../security/complete-kyc" class="cta">Complete KYC</a>
        </div>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
