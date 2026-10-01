<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$profile=v3Profile($user_email,$user_name);
$client=v3ClientMeta($user_email);
v3PageStart('Profile','profile',$profile,$user_profile_picture);
?>
<section class="v3-heading"><div><span class="v3-kicker">CLIENT PROFILE</span><h1>Personal information</h1><p>Verified profile and KYC information associated with your banking relationship.</p></div><a class="v3-secondary-btn" href="/dashboard-v3/profile-picture/">Update photo</a></section>
<div class="v3-profile-grid">
<section class="v3-panel">
    <div class="v3-profile-hero">
        <?php if($user_profile_picture && $user_profile_picture!=='nil'): ?><img src="/dashboard/security/complete-kyc/uploads/<?php echo htmlspecialchars($user_profile_picture); ?>" alt="Profile"><?php else: ?><span class="v3-profile-avatar"><?php echo htmlspecialchars(strtoupper(substr($profile['name'],0,1))); ?></span><?php endif; ?>
        <div><span class="v3-kicker">ACCOUNT HOLDER</span><h2><?php echo htmlspecialchars($profile['name']); ?></h2><p><?php echo htmlspecialchars($profile['occupation']); ?></p></div>
        <span class="v3-status successful"><?php echo htmlspecialchars($profile['status']); ?></span>
    </div>
    <div class="v3-meta-grid" style="margin-top:16px">
        <div><span>Customer number</span><strong><?php echo htmlspecialchars($client['customer_number']); ?></strong></div>
        <div><span>Relationship status</span><strong class="v3-verified"><?php echo htmlspecialchars($client['user_status']); ?></strong></div>
        <div><span>Member since</span><strong><?php echo htmlspecialchars($client['member_since']); ?></strong></div>
    </div>
    <dl class="v3-profile-details">
        <div><dt>Date of birth</dt><dd><?php echo htmlspecialchars($profile['dob']); ?></dd></div>
        <div><dt>Phone number</dt><dd><?php echo htmlspecialchars($profile['phone']); ?></dd></div>
        <div><dt>Occupation</dt><dd><?php echo htmlspecialchars($profile['occupation']); ?></dd></div>
        <div><dt>Source of income</dt><dd><?php echo htmlspecialchars($profile['source_of_income']); ?></dd></div>
        <div><dt>Nationality</dt><dd><?php echo htmlspecialchars($profile['nationality']); ?></dd></div>
        <div><dt>Country of residence</dt><dd><?php echo htmlspecialchars($profile['country']); ?></dd></div>
        <div class="wide"><dt>Residential address</dt><dd><?php echo htmlspecialchars($profile['address'].', '.$profile['city']); ?></dd></div>
    </dl>
</section>
<aside class="v3-panel v3-profile-actions">
    <span class="v3-kicker">PROFILE CONTROLS</span><h2>Manage your details</h2>
    <p>Some verified KYC details require bank review before they can be changed.</p>
    <a href="/dashboard-v3/profile-picture/"><span class="material-symbols-rounded">photo_camera</span><div><strong>Profile picture</strong><small>Change your account photo</small></div></a>
    <a href="/dashboard-v3/identity/"><span class="material-symbols-rounded">badge</span><div><strong>KYC information</strong><small>Review verification information</small></div></a>
    <a href="/dashboard-v3/support/"><span class="material-symbols-rounded">support_agent</span><div><strong>Request profile change</strong><small>Open an authenticated support case</small></div></a>
</aside>
</div>
<?php v3PageEnd(); ?>