<?php
define('VELMORA_SIGNUP_SUCCESS_TARGET','/onboarding/?registered=true');
require_once('../signup/app.php');
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Open an Account | Velmora Bank</title>
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<div class="pv2-demo">DEMO ENVIRONMENT</div>
<main class="pv2-auth-page">
<section class="pv2-auth-side">
  <a href="/"><img class="pv2-auth-brand" src="/assets/images/branding/logo.png" alt="Velmora Bank"></a>
  <div class="pv2-auth-copy"><span class="pv2-eyebrow">OPEN A VELMORA PROFILE</span><h1>Start the relationship with a clean first step.</h1><p>Create your secure profile first. After sign-in, you can open banking accounts and complete identity information inside the banking workspace.</p>
    <div class="pv2-auth-points">
      <div><span class="material-symbols-rounded">person_add</span><div><strong>1. Create profile</strong><small>Name, email and a password establish your online profile.</small></div></div>
      <div><span class="material-symbols-rounded">account_balance</span><div><strong>2. Open account</strong><small>Choose the account type and supported currency you need.</small></div></div>
      <div><span class="material-symbols-rounded">badge</span><div><strong>3. Complete verification</strong><small>Provide the information required for the KYC profile.</small></div></div>
    </div>
  </div>
  <div class="pv2-auth-foot">Velmora Bank · Demo environment</div>
</section>
<section class="pv2-auth-main">
  <div class="pv2-auth-card">
    <a class="pv2-auth-home" href="/"><span class="material-symbols-rounded">arrow_back</span>Back to Velmora</a>
    <h2>Create your profile</h2><p>This creates online access. Banking accounts are opened separately after you sign in.</p>
    <?php if(!empty($_GET['alert_info_section'])): ?><div class="pv2-alert error"><?php echo htmlspecialchars(trim(strip_tags((string)$_GET['alert_info_section']))); ?></div><?php endif; ?>
    <form method="post" class="pv2-form">
      <label><span>Full legal name</span><input type="text" name="full_name" autocomplete="name" required placeholder="Your full name"></label>
      <label><span>Email address</span><input type="email" name="email" autocomplete="email" required placeholder="you@example.com"></label>
      <label><span>Create password</span><input type="password" name="password" autocomplete="new-password" minlength="8" required placeholder="At least 8 characters"></label>
      <label class="pv2-auth-check"><input type="checkbox" required> I have read and agree to the <a class="pv2-auth-link" href="/terms/" target="_blank">Terms of Use</a>.</label>
      <button class="pv2-btn primary pv2-auth-submit" type="submit" name="sign_up" value="sign-up">Create profile</button>
    </form>
    <div class="pv2-auth-divider"></div>
    <div class="pv2-auth-bottom">Already registered? <a href="/login/">Sign in</a></div>
  </div>
</section>
</main></body></html>