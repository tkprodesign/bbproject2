<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$profile=v3Profile($user_email,$user_name);
v3PageStart('Security','security',$profile,$user_profile_picture);
?>
<section class="v3-heading"><div><span class="v3-kicker">SECURITY CENTER</span><h1>Account security</h1><p>Manage credentials and security preferences without leaving the V3 banking workspace.</p></div></section>
<div class="v3-security-grid">
    <section class="v3-panel v3-security-score"><span class="material-symbols-rounded">verified_user</span><div><span class="v3-kicker">ACCOUNT PROTECTION</span><h2>Security controls active</h2><p>Your authenticated banking session and KYC profile are active. Use the controls below for credential changes.</p></div></section>
    <section class="v3-panel">
        <div class="v3-section-head"><div><span class="v3-kicker">CONTROLS</span><h2>Security settings</h2></div></div>
        <div class="v3-settings-list">
            <a href="/dashboard/security/change-password/"><span class="material-symbols-rounded">password</span><div><strong>Change password</strong><small>Update your online banking password</small></div><span class="material-symbols-rounded">chevron_right</span></a>
            <a href="/dashboard/security/preferences/"><span class="material-symbols-rounded">tune</span><div><strong>Security preferences</strong><small>Manage account security options</small></div><span class="material-symbols-rounded">chevron_right</span></a>
            <a href="/contact/"><span class="material-symbols-rounded">support_agent</span><div><strong>Security support</strong><small>Contact the bank if something looks wrong</small></div><span class="material-symbols-rounded">chevron_right</span></a>
        </div>
    </section>
</div>
<?php v3PageEnd(); ?>