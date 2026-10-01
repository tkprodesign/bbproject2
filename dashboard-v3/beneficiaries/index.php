<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$profile=v3Profile($user_email,$user_name);
$beneficiaries=v3Beneficiaries($user_email,true);
v3PageStart('Beneficiaries','beneficiaries',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
    <div><span class="v3-kicker">PAYEE DIRECTORY</span><h1>Beneficiaries</h1><p>Keep trusted recipient details on file and reuse them when preparing transfers.</p></div>
    <a class="v3-primary-btn" href="/dashboard-v3/transfer/"><span class="material-symbols-rounded">north_east</span>New transfer</a>
</section>
<div class="v3-two-col beneficiaries-page">
<section class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">SAVED PAYEES</span><h2><?php echo count($beneficiaries); ?> active</h2></div></div>
    <div class="v3-beneficiary-list">
        <?php foreach($beneficiaries as $b): ?>
        <article>
            <span class="v3-beneficiary-avatar"><?php echo htmlspecialchars(strtoupper(substr((string)$b['beneficiary_name'],0,1))); ?></span>
            <div class="v3-beneficiary-main"><strong><?php echo htmlspecialchars($b['beneficiary_name']); ?></strong><small><?php echo htmlspecialchars($b['bank_name']); ?> · •••• <?php echo htmlspecialchars(substr((string)$b['account_number'],-4)); ?></small></div>
            <div><span>Currency</span><strong><?php echo htmlspecialchars($b['currency']); ?></strong></div>
            <div><span>Last used</span><strong><?php echo $b['last_used_at']?htmlspecialchars(date('M d, Y',strtotime((string)$b['last_used_at']))):'Never'; ?></strong></div>
            <div class="v3-beneficiary-actions">
                <a href="/dashboard-v3/transfer/?beneficiary=<?php echo (int)$b['id']; ?>">Pay</a>
                <form method="post" onsubmit="return confirm('Remove this beneficiary?');"><?php echo v3CsrfInput(); ?><input type="hidden" name="beneficiary_id" value="<?php echo (int)$b['id']; ?>"><button type="submit" name="v3_remove_beneficiary" value="1">Remove</button></form>
            </div>
        </article>
        <?php endforeach; ?>
        <?php if(empty($beneficiaries)): ?><div class="v3-empty">No saved beneficiaries yet.</div><?php endif; ?>
    </div>
</section>
<aside class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">ADD PAYEE</span><h2>Save beneficiary</h2></div></div>
    <form method="post" class="v3-form">
        <?php echo v3CsrfInput(); ?>
        <label><span>Beneficiary name</span><input type="text" name="beneficiary_name" required></label>
        <label><span>Nickname <small>(optional)</small></span><input type="text" name="nickname"></label>
        <label><span>Bank name</span><input type="text" name="bank_name" required></label>
        <label><span>Account number</span><input type="text" name="account_number" required></label>
        <div class="v3-form-row">
            <label><span>Account type</span><select name="account_type"><option>Current</option><option>Savings</option><option>Not Sure</option></select></label>
            <label><span>Currency</span><select name="currency" required><?php echo velmoraCurrencyOptions('USD'); ?></select></label>
        </div>
        <button type="submit" name="v3_add_beneficiary" value="1" class="v3-primary-btn">Save beneficiary</button>
    </form>
</aside>
</div>
<?php v3PageEnd(); ?>