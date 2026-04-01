<?php include('app.php')?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/images/branding/icon.png">
    <title>Sign In</title>
    
    <link rel="stylesheet" href="/assets/stylesheets/sign-in.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,1,0" />
</head>
<body>
<?php
    echo $_GET['alert_info_section'];
?>
<section class="sign-in">
    <div class="container">
        <div class="content">
            <form action="" method="post">
                <div class="logo">
                    <h2>Velmora Bank</h2>
                </div>
                <h3>Hello! let's get started</h3>
                <p>Sign in to continue.</p>
                <div class="error" style="display: <?php echo (isset($_GET['error']) && $_GET['error'] == 'yes') ? 'block' : 'none'; ?>;">
                    <p>Email or Password Incorrect</p>
                </div>
                <div class="input">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="sign_in" value="sign-in">SIGN IN</button>
                <div class="checkbox">
                    <label for="remember_me"><input type="checkbox" name="remember_me" value="1"><span>Keep me signed in</span></label>
                    <a href="#">Forgot password?</a>
                </div>
                <div class="footer">
                    <span>Don't have an account? <a href="/sign-up">Create</a></span>
                </div>
            </form>
        </div>
    </div>
</section>
</body>
</html>