<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';

$profile=v3Profile($user_email,$user_name);
$preferences=v3Preferences($user_email);

v3PageStart('Preferences','preferences',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading"><div><span class="v3-kicker">BANKING PREFERENCES</span><h1>Alerts & delivery</h1><p>Choose how the banking experience handles in-app notices, email alerts and statement delivery.</p></div></section>
<div class="v3-two-col">
  <section class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">NOTIFICATIONS</span><h2>Communication preferences</h2></div></div>
    <form method="post" class="v3-preferences">
      <?php echo v3CsrfInput(); ?>
      <div class="v3-pref-row"><div><strong>Transaction email alerts</strong><small>Email notices for banking activity where supported.</small></div><label class="v3-switch"><input type="checkbox" name="email_transaction_alerts" <?php echo (int)$preferences['email_transaction_alerts']===1?'checked':''; ?>><span></span></label></div>
      <div class="v3-pref-row"><div><strong>Security email alerts</strong><small>Email notices for important account-security events where supported.</small></div><label class="v3-switch"><input type="checkbox" name="email_security_alerts" <?php echo (int)$preferences['email_security_alerts']===1?'checked':''; ?>><span></span></label></div>
      <div class="v3-pref-row"><div><strong>In-app notifications</strong><small>Store account notices in your online banking message center.</small></div><label class="v3-switch"><input type="checkbox" name="in_app_notifications" <?php echo (int)$preferences['in_app_notifications']===1?'checked':''; ?>><span></span></label></div>
      <div class="v3-pref-row"><div><strong>Statement delivery</strong><small>Preferred delivery mode for future statement workflows.</small></div><select name="statement_delivery"><option value="Digital" <?php echo $preferences['statement_delivery']==='Digital'?'selected':''; ?>>Digital</option><option value="Email" <?php echo $preferences['statement_delivery']==='Email'?'selected':''; ?>>Email</option></select></div>
      <div class="v3-pref-row"><div><strong>Timezone</strong><small>Used as your preferred presentation timezone.</small></div><select name="timezone"><option value="America/New_York" <?php echo $preferences['timezone']==='America/New_York'?'selected':''; ?>>America/New_York</option><option value="Africa/Lagos" <?php echo $preferences['timezone']==='Africa/Lagos'?'selected':''; ?>>Africa/Lagos</option><option value="Europe/London" <?php echo $preferences['timezone']==='Europe/London'?'selected':''; ?>>Europe/London</option><option value="Europe/Amsterdam" <?php echo $preferences['timezone']==='Europe/Amsterdam'?'selected':''; ?>>Europe/Amsterdam</option><option value="UTC" <?php echo $preferences['timezone']==='UTC'?'selected':''; ?>>UTC</option></select></div>
      <button class="v3-primary-btn" type="submit" name="v3_save_preferences" value="1">Save preferences</button>
    </form>
  </section>
  <aside class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">IMPORTANT</span><h2>Security messages</h2></div></div>
    <p class="v3-form-note">Some security-critical communications may still need to be shown or sent regardless of ordinary marketing/notification preferences where operationally necessary.</p>
    <div class="v3-settings-list">
      <a href="/dashboard-v3/security/"><span class="material-symbols-rounded">shield</span><div><strong>Security center</strong><small>Review recent account security activity.</small></div><span class="material-symbols-rounded">chevron_right</span></a>
      <a href="/dashboard-v3/notifications/"><span class="material-symbols-rounded">notifications</span><div><strong>Notification center</strong><small>Review your stored banking notices.</small></div><span class="material-symbols-rounded">chevron_right</span></a>
    </div>
  </aside>
</div>
<?php v3PageEnd(); ?>