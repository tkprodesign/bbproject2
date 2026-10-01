<?php
require_once __DIR__ . '/../common-sections/app.php';

$sent=false;$error='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['request_reset'])){
    $email=strtolower(trim((string)($_POST['email']??'')));
    $sent=true;
    if(filter_var($email,FILTER_VALIDATE_EMAIL)){
        $db=connectToDatabase();
        $stmt=$db->prepare("SELECT id,name FROM users WHERE LOWER(email)=LOWER(?) LIMIT 1");
        $stmt->bind_param('s',$email);$stmt->execute();$user=$stmt->get_result()->fetch_assoc()?:null;$stmt->close();

        if($user){
            $db->query("DELETE FROM password_reset_tokens WHERE expires_at < NOW() OR used_at IS NOT NULL");
            $recent=false;
            $stmt=$db->prepare("SELECT id FROM password_reset_tokens WHERE LOWER(user_email)=LOWER(?) AND created_at>DATE_SUB(NOW(),INTERVAL 2 MINUTE) LIMIT 1");
            $stmt->bind_param('s',$email);$stmt->execute();$recent=(bool)$stmt->get_result()->fetch_assoc();$stmt->close();

            if(!$recent){
            $stmt=$db->prepare("DELETE FROM password_reset_tokens WHERE LOWER(user_email)=LOWER(?) AND used_at IS NULL");
            $stmt->bind_param('s',$email);$stmt->execute();$stmt->close();

            $token=bin2hex(random_bytes(32));
            $hash=hash('sha256',$token);
            $expiresAt=date('Y-m-d H:i:s',time()+1800);
            $ip=substr((string)($_SERVER['REMOTE_ADDR']??''),0,45);

            $stmt=$db->prepare("INSERT INTO password_reset_tokens (user_email,token_hash,expires_at,requested_ip) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss',$email,$hash,$expiresAt,$ip);$stmt->execute();$stmt->close();

            $resetUrl='https://velmorabank.us/reset-password/?token='.urlencode($token);
            $safeName=htmlspecialchars((string)$user['name'],ENT_QUOTES,'UTF-8');
            $safeUrl=htmlspecialchars($resetUrl,ENT_QUOTES,'UTF-8');
            $body='<!doctype html><html><body style="font-family:Arial,sans-serif;background:#f3f6f8;padding:24px">
            <div style="max-width:620px;margin:auto;background:#fff;border:1px solid #e4e9ef;border-radius:14px;overflow:hidden">
              <div style="background:#0b2239;padding:22px 24px;color:#fff"><strong>Velmora Bank</strong></div>
              <div style="padding:26px">
                <h2 style="margin-top:0;color:#172536">Reset your online banking password</h2>
                <p>Hello '.$safeName.',</p>
                <p>We received a request to reset your Velmora online banking password. This link expires in 30 minutes and can be used once.</p>
                <p style="margin:26px 0"><a href="'.$safeUrl.'" style="display:inline-block;background:#0b2239;color:#fff;text-decoration:none;padding:12px 18px;border-radius:8px;font-weight:bold">Reset password</a></p>
                <p>If you did not request this, you can ignore this email. Your existing password remains unchanged.</p>
                <p style="font-size:12px;color:#6f7b88">For your security, Velmora support should never ask you to send your full password or a one-time code.</p>
              </div>
            </div></body></html>';

            if(!sendSiteEmail($email,'Reset your Velmora password',$body)){
                error_log('Password reset email delivery failed for a registered profile.');
            }
            recordSecurityEvent($db,$email,'Password Reset Requested','Password reset link requested');
            }
        }
        $db->close();
    }
}
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Forgot Password | Velmora Bank</title>
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<div class="pv2-demo">DEMO ENVIRONMENT</div>
<main class="pv2-auth-page">
<section class="pv2-auth-side">
  <a href="/"><img class="pv2-auth-brand" src="/assets/images/branding/logo.png" alt="Velmora Bank"></a>
  <div class="pv2-auth-copy"><span class="pv2-eyebrow">ACCOUNT ACCESS</span><h1>Recover access without exposing your account.</h1><p>Enter the email registered with your Velmora profile. If a matching profile exists, a single-use reset link will be sent to that address.</p>
    <div class="pv2-auth-points">
      <div><span class="material-symbols-rounded">timer</span><div><strong>30-minute link</strong><small>The reset link expires automatically.</small></div></div>
      <div><span class="material-symbols-rounded">key</span><div><strong>Single use</strong><small>A completed reset invalidates the token.</small></div></div>
      <div><span class="material-symbols-rounded">visibility_off</span><div><strong>No account disclosure</strong><small>The response does not reveal whether an email is registered.</small></div></div>
    </div>
  </div>
  <div class="pv2-auth-foot">Velmora Bank · Demo environment</div>
</section>
<section class="pv2-auth-main">
  <div class="pv2-auth-card">
    <a class="pv2-auth-home" href="/login/"><span class="material-symbols-rounded">arrow_back</span>Back to sign in</a>
    <h2>Reset your password</h2>
    <p>We will send instructions to the registered email address when a matching profile exists.</p>
    <?php if($sent): ?>
      <div class="pv2-alert success">If an account matches that email address, password-reset instructions have been sent.</div>
      <div class="pv2-auth-bottom" style="text-align:left">Didn’t receive anything? Check spam, wait a few minutes, then try again or <a href="/support/">contact support</a>.</div>
    <?php else: ?>
      <form method="post" class="pv2-form">
        <label><span>Email address</span><input type="email" name="email" autocomplete="email" required placeholder="you@example.com"></label>
        <button class="pv2-btn primary pv2-auth-submit" type="submit" name="request_reset" value="1">Send reset instructions</button>
      </form>
    <?php endif; ?>
  </div>
</section>
</main></body></html>