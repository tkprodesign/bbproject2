<?php include('app.php')?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/assets/images/branding/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/icon.png">
    <title>Sign Up | Velmora Bank</title>

    <link rel="stylesheet" href="/assets/stylesheets/desktop/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/desktop/sign-in.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/sign-in.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/sign-in.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0" />
</head>
<body>
<?php include('../common-sections/header.php'); ?>
<?php echo $_GET['alert_info_section']; ?>
<section class="sign-up sign-in auth-page">
    <div class="container">
        <div class="content">
            <aside class="auth-panel">
                <img src="/assets/images/branding/velmora/logo.png" alt="Velmora Bank logo" class="brand-logo">
                <h1>Open your account in minutes</h1>
                <p>Join thousands of customers who trust Velmora Bank for personal and business banking.</p>
                <ul>
                    <li><span class="material-symbols-outlined filled">account_balance</span>Checking, savings, and cards</li>
                    <li><span class="material-symbols-outlined filled">payments</span>Fast local and international transfers</li>
                    <li><span class="material-symbols-outlined filled">monitoring</span>Real-time activity notifications</li>
                </ul>
            </aside>
            <form action="" method="post" class="auth-form">
                <div class="logo">
                    <h2>Create your account</h2>
                    <p>It only takes a few steps to get started.</p>
                </div>
                <div class="input">
                    <label for="full_name">Full Name</label>
                    <input id="full_name" type="text" name="full_name" placeholder="John Doe" required>
                </div>
                <div class="input">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" placeholder="you@example.com" required>
                </div>
                <div class="input">
                    <label for="password">Create Password</label>
                    <input id="password" type="password" name="password" placeholder="Use at least 8 characters" required>
                </div>
                <div class="checkbox">
                    <label for="terms"><input id="terms" type="checkbox" name="remember_me" required><span>I agree to the Terms &amp; Conditions</span></label>
                </div>

                <button type="submit" name="sign_up" value="sign-up">Create Account</button>

                <div class="footer">
                    <span>Already have an account? <a href="/login">Sign in</a></span>
                </div>
            </form>
        </div>
    </div>
</section>
<?php include('../common-sections/footer.php'); ?>
<?php include('../common-sections/smartsupp-live-chat.html'); ?>
</body>
</html>
