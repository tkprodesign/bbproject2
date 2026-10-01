<?php
define('VELMORA_LOGIN_TARGET','/dashboard-v3/');
require_once('../login/app.php');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Sign In | Velmora Bank</title>
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<div class="pv2-demo">DEMO ENVIRONMENT</div>
<main class="pv2-auth-page">
<section class="pv2-auth-side">
  <a href="/"><img class="pv2-auth-brand" src="/assets/images/branding/logo.png" alt="Velmora Bank"></a>
  <div class="pv2-auth-copy"><span class="pv2-eyebrow">SECURE ONLINE BANKING</span><h1>Welcome back to your banking workspace.</h1><p>Sign in to review your accounts, transactions, transfers, foreign-exchange activity, notifications and profile information.</p>
    <div class="pv2-auth-points">
      <div><span class="material-symbols-rounded">account_balance_wallet</span><div><strong>Account-level balances</strong><small>Each account keeps its actual currency and ledger activity.</small></div></div>
      <div><span class="material-symbols-rounded">currency_exchange</span><div><strong>Reviewed money movement</strong><small>Transfers and FX use review steps before final execution.</small></div></div>
      <div><span class="material-symbols-rounded">shield_lock</span><div><strong>Security visibility</strong><small>Profile and security activity remain part of the relationship.</small></div></div>
    </div>
  </div>
  <div class="pv2-auth-foot">Velmora Bank · Demo environment</div>
</section>
<section class="pv2-auth-main">
  <div class="pv2-auth-card">
    <a class="pv2-auth-home" href="/"><span class="material-symbols-rounded">arrow_back</span>Back to Velmora</a>
    <h2>Sign in</h2><p>Use the email address and password registered with your Velmora profile.</p>
    <?php if(isset($_GET['error'])&&$_GET['error']==='yes'): ?><div class="pv2-alert error">The email or password was not accepted. Check your details and try again.</div><?php endif; ?>
    <form method="post" class="pv2-form">
      <label><span>Email address</span><input type="email" name="email" autocomplete="username" required placeholder="you@example.com"></label>
      <label><span>Password</span><input type="password" name="password" autocomplete="current-password" required placeholder="Enter your password"></label>
      <div class="pv2-auth-row"><label class="pv2-auth-check"><input type="checkbox" name="remember_me" value="1">Keep me signed in</label><a class="pv2-auth-link" href="/forgot-password-v2/">Forgot password?</a></div>
      <button class="pv2-btn primary pv2-auth-submit" type="submit" name="sign_in" value="sign-in">Sign in securely</button>
    </form>
    <div class="pv2-auth-divider"></div>
    <div class="pv2-auth-bottom">New to Velmora? <a href="/signup-v2/">Open an account</a></div>
  </div>
</section>
</main></body></html>