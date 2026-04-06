<?php include('app.php')?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <title>Sign In | Velmora Bank</title>

    <link rel="stylesheet" href="/assets/stylesheets/site.css">
    <link rel="stylesheet" href="/assets/stylesheets/sign-in.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0" />
</head>
<body>
<?php include('../common-sections/header.php'); ?>
<?php echo $_GET['alert_info_section']; ?>
<section class="sign-in auth-page">
    <div class="container">
        <div class="content">
            <aside class="auth-panel">
                <img src="/assets/images/branding/velmora/logo.png" alt="Velmora Bank logo" class="brand-logo">
                <h1>Secure online banking</h1>
                <p>Access your accounts, monitor transactions, and manage cards with bank-grade protection.</p>
                <ul>
                    <li><span class="material-symbols-outlined filled">verified_user</span>256-bit encrypted sessions</li>
                    <li><span class="material-symbols-outlined filled">shield_lock</span>Continuous fraud monitoring</li>
                    <li><span class="material-symbols-outlined filled">support_agent</span>24/7 client support</li>
                </ul>
            </aside>
            <form action="" method="post" class="auth-form">
                <div class="logo">
                    <h2>Welcome back</h2>
                    <p>Sign in to continue to your dashboard.</p>
                </div>
                <div class="error" style="display: <?php echo (isset($_GET['error']) && $_GET['error'] == 'yes') ? 'block' : 'none'; ?>;">
                    <p>Email or password is incorrect. Please try again.</p>
                </div>
                <div class="input">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="input">
                    <label for="password">Password</label>
                    <input id="password" type="password" name="password" placeholder="Enter your password" required>
                </div>
                <button type="submit" name="sign_in" value="sign-in">Sign In Securely</button>
                <div class="checkbox">
                    <label for="remember_me"><input id="remember_me" type="checkbox" name="remember_me" value="1"><span>Keep me signed in</span></label>
                    <a href="#">Forgot password?</a>
                </div>
                <div class="footer">
                    <span>Don&apos;t have an account? <a href="/sign-up">Create one</a></span>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include('../common-sections/smartsupp-live-chat.html'); ?>
</body>
</html>
