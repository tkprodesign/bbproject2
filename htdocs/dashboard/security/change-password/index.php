<?php include('../../app.php');?>
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
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html')?>
<section class="change-password">
    <div class="container">
        <div class="heading">
            <p>Change Security Password</p>
        </div>
        <div class="content">
            <form action="" method="post">
                <div class="input-box">
                    <label>Old Password</label>
                    <input type="text">
                </div>
                <div class="input-box">
                    <label>New Password</label>
                    <input type="text">
                </div>
                <div class="input-box">
                    <label>Confirm New Password</label>
                    <input type="text">
                </div>
            </form>
        </div>
        <div class="footer">
            <a href="#" class="cta">Update Password</a>
        </div>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>