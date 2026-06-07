<?php include('../../app.php'); ?>
<?php
$pwError   = '';
$pwSuccess = false;

if (isset($_POST['change_password'])) {
    $oldPw  = $_POST['old_password']  ?? '';
    $newPw  = $_POST['new_password']  ?? '';
    $confPw = $_POST['confirm_password'] ?? '';

    if (empty($oldPw) || empty($newPw) || empty($confPw)) {
        $pwError = 'All fields are required.';
    } elseif (strlen($newPw) < 8) {
        $pwError = 'New password must be at least 8 characters.';
    } elseif ($newPw !== $confPw) {
        $pwError = 'New password and confirmation do not match.';
    } else {
        $dbpw = connectToDatabase();
        $stmt = $dbpw->prepare("SELECT password FROM users WHERE email = ?");
        $stmt->bind_param('s', $user_email);
        $stmt->execute();
        $stmt->bind_result($hashedPw);
        $stmt->fetch();
        $stmt->close();

        if (!password_verify($oldPw, $hashedPw)) {
            $pwError = 'Your current password is incorrect.';
        } else {
            $newHash = password_hash($newPw, PASSWORD_BCRYPT);
            $upd = $dbpw->prepare("UPDATE users SET password = ? WHERE email = ?");
            $upd->bind_param('ss', $newHash, $user_email);
            if ($upd->execute()) {
                $pwSuccess = true;
            } else {
                $pwError = 'Could not update password. Please try again.';
            }
            $upd->close();
        }
        $dbpw->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
    <title>Change Password | Velmora Bank</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200">
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html') ?>
<section class="change-password">
    <div class="container">
        <div class="heading">
            <p>Change Security Password</p>
        </div>

        <?php if ($pwSuccess): ?>
            <div class="content">
                <p style="color:#1a7a4d;background:#edfdf7;border:1px solid #b8ebde;border-radius:8px;padding:14px 18px;font-weight:600;">
                    &#10003; Your password has been updated successfully.
                </p>
            </div>
            <div class="footer">
                <a href="/dashboard" class="cta">Back to Dashboard</a>
            </div>
        <?php else: ?>
            <?php if ($pwError): ?>
                <p style="color:#8a1f17;background:#fff2f1;border:1px solid #ffd0cc;border-radius:8px;padding:12px 18px;margin-bottom:4px;">
                    <?php echo htmlspecialchars($pwError); ?>
                </p>
            <?php endif; ?>
            <div class="content">
                <form action="" method="post">
                    <div class="input-box">
                        <label>Old Password</label>
                        <input type="password" name="old_password" autocomplete="current-password" required>
                    </div>
                    <div class="input-box">
                        <label>New Password</label>
                        <input type="password" name="new_password" autocomplete="new-password" required>
                    </div>
                    <div class="input-box">
                        <label>Confirm New Password</label>
                        <input type="password" name="confirm_password" autocomplete="new-password" required>
                    </div>
                    <div class="footer" style="margin-top:16px;">
                        <button type="submit" name="change_password" value="1" class="cta"
                                style="background:none;border:none;cursor:pointer;padding:0;font:inherit;">
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>
    </div>
</section>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>
