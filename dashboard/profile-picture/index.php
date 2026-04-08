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
</head>
<body>
<?php include('../../common-sections/dashboard-header.html')?>
<section class="profile-kyc">
    <div class="container">
        <div class="heading">
            <h1>Profile Picture</h1>
            <p>Upload a clear profile image. This is separate from KYC verification.</p>
        </div>
        <div class="content">
            <?php if ($user_profile_picture && $user_profile_picture !== 'nil'): ?>
                <img src="/dashboard/security/complete-kyc/uploads/<?php echo htmlspecialchars($user_profile_picture); ?>" alt="Current profile picture" class="profile-picture profile-picture-preview">
            <?php else: ?>
                <img src="/assets/images/placeholder-image.png" alt="Current profile picture" class="profile-picture profile-picture-preview">
            <?php endif; ?>
            <form action="" method="post" enctype="multipart/form-data">
                <div class="input-box">
                    <label>Select image (JPG, JPEG, PNG):</label>
                    <input type="file" name="profile_picture" accept="image/*" required>
                </div>
                <div class="input-box">
                    <button type="submit" name="submit_profile_picture" value="1">Update Profile Picture</button>
                </div>
            </form>
            <?php if (!empty($ppstate)): ?>
                <p><?php echo htmlspecialchars($ppstate); ?></p>
            <?php endif; ?>
        </div>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
