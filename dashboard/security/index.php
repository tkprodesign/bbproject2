<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';

$profile=v3Profile($user_email,$user_name);
$client=v3ClientMeta($user_email);

$db=connectToDatabase();
$stmt=$db->prepare("SELECT event_type,description,ip_address,created_at FROM security_events WHERE user_email=? ORDER BY id DESC LIMIT 10");
$stmt->bind_param('s',$user_email);$stmt->execute();$events=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();$db->close();

v3PageStart('Security','security',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading"><div><span class="v3-kicker">SECURITY CENTER</span><h1>Account security</h1><p>Review recent security activity and use the security controls connected to your online banking profile.</p></div></section>

<div class="v3-security-grid">
  <section class="v3-panel v3-security-score">
    <span class="material-symbols-rounded">verified_user</span>
    <div><span class="v3-kicker">ACCOUNT PROTECTION</span><h2><?php echo htmlspecialchars($client['user_status']); ?> relationship</h2><p>Recent sign-ins and selected account-security changes are recorded for review.</p></div>
  </section>
  <section class="v3-panel">
    <div class="v3-meta-grid">
      <div><span>Customer number</span><strong><?php echo htmlspecialchars($client['customer_number']); ?></strong></div>
      <div><span>Last sign-in</span><strong><?php echo $client['last_login_at']?htmlspecialchars(date('M d, Y H:i',strtotime((string)$client['last_login_at']))):'Not recorded'; ?></strong></div>
      <div><span>Sign-in count</span><strong><?php echo (int)$client['login_count']; ?></strong></div>
    </div>
  </section>
</div>

<div class="v3-two-col" style="margin-top:18px">
  <section class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">RECENT ACTIVITY</span><h2>Security events</h2></div></div>
    <div class="v3-security-events">
      <?php foreach($events as $event): ?>
      <article>
        <span class="material-symbols-rounded"><?php echo strtolower((string)$event['event_type'])==='sign in'?'login':'shield'; ?></span>
        <div><strong><?php echo htmlspecialchars($event['event_type']); ?></strong><small><?php echo htmlspecialchars($event['description']); ?><?php if(!empty($event['ip_address'])): ?> · IP <?php echo htmlspecialchars($event['ip_address']); ?><?php endif; ?></small></div>
        <time><?php echo htmlspecialchars(date('M d, Y H:i',strtotime((string)$event['created_at']))); ?></time>
      </article>
      <?php endforeach; ?>
      <?php if(empty($events)): ?><div class="v3-empty">No security events recorded yet.</div><?php endif; ?>
    </div>
  </section>
  <aside class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">CONTROLS</span><h2>Security settings</h2></div></div>
    <div class="v3-settings-list">
      <a href="/dashboard/security/change-password/"><span class="material-symbols-rounded">password</span><div><strong>Change password</strong><small>Update the password used for online banking.</small></div><span class="material-symbols-rounded">chevron_right</span></a>
      <a href="/dashboard/preferences/"><span class="material-symbols-rounded">tune</span><div><strong>Alert preferences</strong><small>Manage stored notification and statement preferences.</small></div><span class="material-symbols-rounded">chevron_right</span></a>
      <a href="/security/"><span class="material-symbols-rounded">policy</span><div><strong>Security & Fraud Center</strong><small>Review public guidance for suspicious activity.</small></div><span class="material-symbols-rounded">chevron_right</span></a>
      <a href="/dashboard/support/"><span class="material-symbols-rounded">support_agent</span><div><strong>Security support</strong><small>Open an authenticated support case if something looks wrong.</small></div><span class="material-symbols-rounded">chevron_right</span></a>
    </div>
  </aside>
</div>
<?php v3PageEnd(); ?>