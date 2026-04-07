<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
</head>
<body>
<div class="pre-header">
    <div class="container">
        <div class="left">
            <p>Your account has not been activated, please complete your KYC to activate your account</p>
        </div>
        <div class="right">
            <a href="#" class="cta">Complete Account Information</a>
            <a href="#" class="close">
                <span class="material-symbols-outlined">
                    close
                    </span>
            </a>
        </div>
    </div>
</div>
<div class="header-gap"></div>
<header>
    <div class="container">
        <a href="#">
            <span class="material-symbols-outlined">
                space_dashboard
                </span>
            <p>Dashboard</p>
        </a>
        <a href="#">
            <span class="material-symbols-outlined">
                payments
                </span>
            <p>Transfer Funds</p>
        </a>
        <a href="#">
            <span class="material-symbols-outlined">
                deployed_code
                </span>
            <p>My Account</p>
        </a>
        <a href="#">
            <span class="material-symbols-outlined">
                notifications
                </span>
            <p>Transaction Alerts <count>0</count></p>
        </a>
        <a href="#">
            <span class="material-symbols-outlined">
                person
                </span>
            <p>Profile</p>
        </a>
        <a href="#"  class="active">
            <span class="material-symbols-outlined">
                admin_panel_settings
                </span>
            <p>Security</p>
        </a>
        <a href="#">
            <span class="material-symbols-outlined">
                logout
                </span>
            <p>Sign Out</p>
        </a>
    </div>
</header>
<section class="preferences">
    <div class="container">
        <div class="heading">
            <p>Security Preferences</p>
        </div>
        <div class="content">
            <div class="row row-1">
                <div>
                    <span>SEND OTP ON TRANSFER</span>
                    <input type="checkbox" name="" id="" checked readonly>
                </div>
            </div>
            <div class="row row-3">
                <div>
                    <span>RECIEVE SECURITY EMAILS </span>
                    <input type="checkbox" name="" id="" checked readonly>
                </div>
            </div>
            <div class="row row-3">
                <div>
                    <span>ENABLE 2 FACTOR AUTHENTICATION</span>
                    <input type="checkbox" name="" id="">
                </div>
                <div>
                    <p>If you enable this, an email will be sent to your email address whenever you want to login</p>
                </div>
            </div>
        </div>
        <div class="footer">
            <a href="#" class="cta">Update Preferences</a>
        </div>
    </div>
</section>
</div>
</body>
</html>