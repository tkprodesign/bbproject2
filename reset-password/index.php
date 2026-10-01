<?php
require_once __DIR__ . '/../common-sections/app.php';

$token=trim((string)($_GET['token']??$_POST['token']??''));
$valid=false;$email='';$errors=[];$done=false;

if(preg_match('/^[a-f0-9]{64}$/i',$token)){
    $hash=hash('sha256',$token);
    $db=connectToDatabase();
    $stmt=$db->prepare("SELECT user_email FROM password_reset_tokens WHERE token_hash=? AND used_at IS NULL AND expires_at>NOW() LIMIT 1");
    $stmt->bind_param('s',$hash);$stmt->execute();$stmt->bind_result($email);$valid=$stmt->fetch();$stmt->close();$db->close();
}

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['reset_password'])){
    $new=(string)($_POST['new_password']??'');$confirm=(string)($_POST['confirm_password']??'');
    if(!$valid)$errors[]='This reset link is invalid or has expired.';
    if(strlen($new)<8)$errors[]='Use at least 8 characters.';
    if($new!==$confirm)$errors[]='The password confirmation does not match.';
    if(empty($errors)){
        $db=connectToDatabase();
        $db->begin_transaction();
        try{
            $newHash=password_hash($new,PASSWORD_DEFAULT);
            $stmt=$db->prepare("UPDATE users SET password=? WHERE email=?");
            $stmt->bind_param('ss',$newHash,$email);$stmt->execute();$stmt->close();

            $stmt=$db->prepare("UPDATE password_reset_tokens SET used_at=NOW() WHERE token_hash=? AND used_at IS NULL");
            $stmt->bind_param('s',$hash);$stmt->execute();$stmt->close();

            $stmt=$db->prepare("UPDATE password_reset_tokens SET used_at=NOW() WHERE LOWER(user_email)=LOWER(?) AND used_at IS NULL");
            $stmt->bind_param('s',$email);$stmt->execute();$stmt->close();

            recordSecurityEvent($db,$email,'Password Reset','Password reset completed using secure recovery link');
            createUserNotification($db,$email,'Password reset completed','Your online banking password was reset successfully. If you did not make this change, contact support immediately.','Security','/dashboard/security/');

            $db->commit();$done=true;$valid=false;
        }catch(Throwable $e){
            $db->rollback();$errors[]='The password could not be updated. Please request a new reset link.';
            error_log('Password reset failed: '.$e->getMessage());
        }
        $db->close();
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Reset Password | Velmora Bank</title>
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<div class="pv2-demo">DEMO ENVIRONMENT</div>
<main class="pv2-auth-page">
<section class="pv2-auth-side">
  <a href="/"><img class="pv2-auth-brand" src="/assets/images/branding/logo.png" alt="Velmora Bank"></a>
  <div class="pv2-auth-copy"><span class="pv2-eyebrow">SECURE RECOVERY</span><h1>Create a new banking password.</h1><p>A successful reset changes the password attached to your Velmora profile and invalidates the recovery token.</p>
    <div class="pv2-auth-points">
      <div><span class="material-symbols-rounded">password</span><div><strong>Use a unique password</strong><small>Do not reuse a password from another service.</small></div></div>
      <div><span class="material-symbols-rounded">shield_lock</span><div><strong>Security event recorded</strong><small>The completed reset is added to account security history.</small></div></div>
    </div>
  </div>
  <div class="pv2-auth-foot">Velmora Bank · Demo environment</div>
</section>
<section class="pv2-auth-main">
  <div class="pv2-auth-card">
    <a class="pv2-auth-home" href="/login/"><span class="material-symbols-rounded">arrow_back</span>Back to sign in</a>
    <?php if($done): ?>
      <h2>Password updated</h2><p>Your new password is active.</p>
      <div class="pv2-alert success">Your password was reset successfully.</div>
      <a class="pv2-btn primary pv2-auth-submit" href="/login/">Continue to sign in</a>
    <?php elseif(!$valid): ?>
      <h2>Reset link unavailable</h2><p>The link may have expired, already been used, or be incomplete.</p>
      <div class="pv2-alert error">Request a fresh password-reset link to continue.</div>
      <a class="pv2-btn primary pv2-auth-submit" href="/forgot-password/">Request new link</a>
    <?php else: ?>
      <h2>Choose a new password</h2><p>Use at least 8 characters and avoid reusing a password from another service.</p>
      <?php foreach($errors as $error): ?><div class="pv2-alert error"><?php echo htmlspecialchars($error); ?></div><?php endforeach; ?>
      <form method="post" class="pv2-form">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token,ENT_QUOTES,'UTF-8'); ?>">
        <label><span>New password</span><input type="password" name="new_password" minlength="8" autocomplete="new-password" required></label>
        <label><span>Confirm new password</span><input type="password" name="confirm_password" minlength="8" autocomplete="new-password" required></label>
        <button class="pv2-btn primary pv2-auth-submit" type="submit" name="reset_password" value="1">Reset password</button>
      </form>
    <?php endif; ?>
  </div>
</section>
</main></body></html>