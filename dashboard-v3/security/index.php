<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$profile=v3Profile($user_email,$user_name);
$client=v3ClientMeta($user_email);
$preferences=v3UserPreferences($user_email);

$db=connectToDatabase();
$stmt=$db->prepare("SELECT event_type,description,ip_address,user_agent,created_at FROM security_events WHERE user_email=? ORDER BY id DESC LIMIT 8");
$stmt->bind_param('s',$user_email);
$stmt->execute();
$securityEvents=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
$db->close();

v3PageStart('Security','security',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
    <div><span class="v3-kicker">SECURITY CENTER</span><h1>Account security</h1><p>Monitor sign-in activity, manage notification preferences and control how account information is delivered.</p></div>
</section>

<div class="v3-security-grid">
    <section class="v3-panel">
        <div class="v3-security-score">
            <span class="material-symbols-rounded">verified_user</span>
            <div><span class="v3-kicker">ACCOUNT PROTECTION</span><h2>Security controls active</h2><p>Your banking profile is active and recent sign-ins are recorded for security review.</p></div>
        </div>
        <div class="v3-meta-grid">
            <div><span>Customer number</span><strong><?php echo htmlspecialchars($client['customer_number']); ?></strong></div>
            <div><span>Last sign-in</span><strong><?php echo $client['last_login_at']?htmlspecialchars(date('M d, Y H:i',strtotime((string)$client['last_login_at']))):'Not recorded'; ?></strong></div>
            <div><span>Sign-in count</span><strong><?php echo (int)$client['login_count']; ?></strong></div>
        </div>
    </section>

    <section class="v3-panel">
        <div class="v3-section-head"><div><span class="v3-kicker">CONTROLS</span><h2>Security settings</h2></div></div>
        <div class="v3-settings-list">
            <a href="/dashboard/security/change-password/"><span class="material-symbols-rounded">password</span><div><strong>Change password</strong><small>Update your online banking password</small></div><span class="material-symbols-rounded">chevron_right</span></a>
            <a href="/contact/"><span class="material-symbols-rounded">support_agent</span><div><strong>Security support</strong><small>Contact the bank if something looks wrong</small></div><span class="material-symbols-rounded">chevron_right</span></a>
        </div>
    </section>
</div>

<div class="v3-two-col" style="margin-top:18px">
    <section class="v3-panel">
        <div class="v3-section-head"><div><span class="v3-kicker">RECENT ACTIVITY</span><h2>Security events</h2></div></div>
        <div class="v3-security-events">
            <?php foreach($securityEvents as $event): ?>
            <article>
                <span class="material-symbols-rounded"><?php echo strtolower((string)$event['event_type'])==='sign in'?'login':'shield'; ?></span>
                <div><strong><?php echo htmlspecialchars($event['event_type']); ?></strong><small><?php echo htmlspecialchars($event['description']); ?><?php if(!empty($event['ip_address'])): ?> · IP <?php echo htmlspecialchars($event['ip_address']); ?><?php endif; ?></small></div>
                <time><?php echo htmlspecialchars(date('M d, Y H:i',strtotime((string)$event['created_at']))); ?></time>
            </article>
            <?php endforeach; ?>
            <?php if(empty($securityEvents)): ?><div class="v3-empty">No security events have been recorded yet.</div><?php endif; ?>
        </div>
    </section>

    <aside class="v3-panel">
        <div class="v3-section-head"><div><span class="v3-kicker">PREFERENCES</span><h2>Alerts & statements</h2></div></div>
        <form method="post" class="v3-preferences">
            <?php echo v3CsrfInput(); ?>
            <div class="v3-pref-row"><div><strong>Transaction email alerts</strong><small>Receive email notices for banking activity</small></div><label class="v3-switch"><input type="checkbox" name="email_transaction_alerts" <?php echo (int)$preferences['email_transaction_alerts']===1?'checked':''; ?>><span></span></label></div>
            <div class="v3-pref-row"><div><strong>Security email alerts</strong><small>Receive sign-in and security notices</small></div><label class="v3-switch"><input type="checkbox" name="email_security_alerts" <?php echo (int)$preferences['email_security_alerts']===1?'checked':''; ?>><span></span></label></div>
            <div class="v3-pref-row"><div><strong>In-app notifications</strong><small>Show notices inside online banking</small></div><label class="v3-switch"><input type="checkbox" name="in_app_notifications" <?php echo (int)$preferences['in_app_notifications']===1?'checked':''; ?>><span></span></label></div>
            <div class="v3-pref-row"><div><strong>Statement delivery</strong><small>Choose how statements are delivered</small></div><select name="statement_delivery"><option value="Digital" <?php echo $preferences['statement_delivery']==='Digital'?'selected':''; ?>>Digital</option><option value="Email" <?php echo $preferences['statement_delivery']==='Email'?'selected':''; ?>>Email</option></select></div>
            <div class="v3-pref-row"><div><strong>Timezone</strong><small>Used for your activity timestamps</small></div><select name="timezone"><option value="UTC" <?php echo $preferences['timezone']==='UTC'?'selected':''; ?>>UTC</option><option value="Africa/Lagos" <?php echo $preferences['timezone']==='Africa/Lagos'?'selected':''; ?>>Africa/Lagos</option><option value="Europe/Amsterdam" <?php echo $preferences['timezone']==='Europe/Amsterdam'?'selected':''; ?>>Europe/Amsterdam</option><option value="America/New_York" <?php echo $preferences['timezone']==='America/New_York'?'selected':''; ?>>America/New_York</option><option value="Europe/London" <?php echo $preferences['timezone']==='Europe/London'?'selected':''; ?>>Europe/London</option></select></div>
            <button type="submit" name="v3_save_preferences" value="1" class="v3-primary-btn">Save preferences</button>
        </form>
    </aside>
</div>
<?php v3PageEnd(); ?>