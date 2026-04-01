<?php include('app.php')?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <title>Sign Up</title>
    
    <link rel="stylesheet" href="/assets/stylesheets/sign-in.css?v=<?php echo time(); ?>">
</head>
<body>
    <?php
        echo $_GET['alert_info_section'];
    ?>
<section class="sing-up sign-in">
    <div class="container">
        <div class="content">
            <form action="" method="post">
                <div class="logo">
                    <h2>Velmora Bank</h2>
                </div> 
                <h3>New here?</h3>
                <p>Signing up is easy.It only takes a few steps.</p>
                <div class="input">
                    <input type="text" name="full_name" placeholder="Full Name" required>
                </div>
                <div class="input">
                    <input type="email" name="email" placeholder="Email" required>
                </div>
                <div class="input">
                    <input type="password" name="password" placeholder="Password" required>
                </div>
                <div class="checkbox">
                    <label for="remember_me"><input type="checkbox" name="remember_me" required><span>I agree to all Terms & Condition</span></label>
                </div>
                
                <button type="submit" name="sign_up" value="sign-up">SIGN UP</button>
                
                <div class="footer">
                    <span>Already have  an account? <a href="/login">Login</a></span>
                </div>
            </form>
        </div>
    </div>
</section>
</body>
</html>