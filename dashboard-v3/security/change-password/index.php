<?php
require_once __DIR__ . '/../../_app.php';
require_once __DIR__ . '/../../_layout.php';

$profile=v3Profile($user_email,$user_name);
$errors=[];

if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['v3_change_password'])){
    v3VerifyPost();
    $current=(string)($_POST['current_password']??'');
    $new=(string)($_POST['new_password']??'');
    $confirm=(string)($_POST['confirm_password']??'');

    if(strlen($new)<8)$errors[]='New password must contain at least 8 characters.';
    if($new!==$confirm)$errors[]='The new password confirmation does not match.';
    if($current===$new && $new!=='')$errors[]='Choose a new password different from the current password.';

    if(empty($errors)){
        $db=connectToDatabase();
        $stmt=$db->prepare("SELECT password FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param('s',$user_email);$stmt->execute();$stmt->bind_result($hash);$found=$stmt->fetch();$stmt->close();

        if(!$found || !password_verify($current,(string)$hash)){
            $errors[]='The current password was not accepted.';
        }else{
            $newHash=password_hash($new,PASSWORD_DEFAULT);
            $stmt=$db->prepare("UPDATE users SET password=? WHERE email=?");
            $stmt->bind_param('ss',$newHash,$user_email);$stmt->execute();$stmt->close();
            recordSecurityEvent($db,$user_email,'Password Changed','Online banking password changed successfully');
            createUserNotification($db,$user_email,'Password changed','Your online banking password was changed. If you did not make this change, contact support immediately.','Security','/dashboard-v3/security/');
            $db->close();
            v3PostMessage('success','Password changed successfully.');
            v3Redirect('/dashboard-v3/security/change-password/');
        }
        if(isset($db) && $db instanceof mysqli) $db->close();
    }
}

v3PageStart('Change Password','security',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
  <div><span class="v3-kicker">SECURITY</span><h1>Change password</h1><p>Use your current password to authorize a new online banking password.</p></div>
  <a class="v3-secondary-btn" href="/dashboard-v3/security/">Back to security</a>
</section>

<div class="v3-two-col">
  <section class="v3-panel">
    <?php foreach($errors as $error): ?><div class="v3-alert error"><span class="material-symbols-rounded">error</span><div><?php echo htmlspecialchars($error); ?></div></div><?php endforeach; ?>
    <form method="post" class="v3-form">
      <?php echo v3CsrfInput(); ?>
      <label><span>Current password</span><input type="password" name="current_password" autocomplete="current-password" required></label>
      <label><span>New password</span><input type="password" name="new_password" autocomplete="new-password" minlength="8" required></label>
      <label><span>Confirm new password</span><input type="password" name="confirm_password" autocomplete="new-password" minlength="8" required></label>
      <button class="v3-primary-btn" type="submit" name="v3_change_password" value="1">Change password</button>
    </form>
  </section>

  <aside class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">PASSWORD GUIDANCE</span><h2>Keep banking credentials separate.</h2></div></div>
    <div class="v3-list">
      <article class="v3-list-item"><span class="v3-list-icon"><span class="material-symbols-rounded">key</span></span><div><strong>Use a unique password</strong><small>Do not reuse a password from email, social media or another financial service.</small></div></article>
      <article class="v3-list-item"><span class="v3-list-icon"><span class="material-symbols-rounded">visibility_off</span></span><div><strong>Never share it</strong><small>Velmora support should not need your full online banking password.</small></div></article>
      <article class="v3-list-item"><span class="v3-list-icon"><span class="material-symbols-rounded">support_agent</span></span><div><strong>Unexpected change?</strong><small>If you did not change your password, contact support immediately from the authenticated support area.</small></div><a href="/dashboard-v3/support/">Support</a></article>
    </div>
  </aside>
</div>
<?php v3PageEnd(); ?>