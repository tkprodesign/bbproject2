<?php include('../../app.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Security Preferences</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html')?>
<section class="preferences">
    <div class="container dashboard-card">
        <div class="heading">
            <p>Security Preferences</p>
        </div>
        <div class="content">
            <div class="row row-1">
                <div>
                    <span>SEND OTP ON TRANSFER</span>
                    <input type="checkbox" checked readonly>
                </div>
            </div>
            <div class="row row-3">
                <div>
                    <span>RECEIVE SECURITY EMAILS</span>
                    <input type="checkbox" checked readonly>
                </div>
            </div>
            <div class="row row-3">
                <div>
                    <span>ENABLE 2-FACTOR AUTHENTICATION</span>
                    <input type="checkbox">
                </div>
                <div>
                    <p>If enabled, you'll receive a security email each time a login attempt is made.</p>
                </div>
            </div>
        </div>
        <div class="footer">
            <a href="#" class="cta">Update Preferences</a>
        </div>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
