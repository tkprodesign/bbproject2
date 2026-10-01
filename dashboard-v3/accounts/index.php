<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$profile=v3Profile($user_email,$user_name);
$accounts=v3Accounts($user_email);
v3PageStart('Accounts','accounts',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
    <div><span class="v3-kicker">BANKING</span><h1>Your accounts</h1><p>Each account has a fixed denomination. Open another currency account before exchanging funds into that currency.</p></div>
</section>

<div class="v3-two-col accounts-page">
    <section class="v3-panel">
        <div class="v3-section-head"><div><span class="v3-kicker">ACTIVE ACCOUNTS</span><h2>Account portfolio</h2></div></div>
        <div class="v3-account-list">
            <?php foreach($accounts as $account): ?>
            <article>
                <div class="v3-account-ident">
                    <span class="material-symbols-rounded">account_balance</span>
                    <div><strong><?php echo htmlspecialchars(!empty($account['account_alias'])?$account['account_alias']:$account['account_type']); ?></strong><small><?php echo htmlspecialchars($account['account_type'].' · '.$account['account_number']); ?></small></div>
                </div>
                <div><span>Currency</span><strong><?php echo htmlspecialchars($account['currency']); ?></strong></div>
                <div><span>Available balance</span><strong><?php echo htmlspecialchars(velmoraFormatCurrency($account['balance'],$account['currency'])); ?></strong></div>
                <div><span>Opened</span><strong><?php echo !empty($account['opened_at'])?htmlspecialchars(date('M d, Y',strtotime((string)$account['opened_at']))):'—'; ?></strong></div>
                <div><span>Status</span><strong class="v3-verified"><?php echo htmlspecialchars($account['account_status']); ?></strong></div>
            </article>
            <?php endforeach; ?>
            <?php if(empty($accounts)): ?><div class="v3-empty">No accounts have been opened.</div><?php endif; ?>
        </div>
    </section>

    <aside class="v3-panel">
        <div class="v3-section-head"><div><span class="v3-kicker">NEW ACCOUNT</span><h2>Open a currency account</h2></div></div>
        <p class="v3-form-note">An account’s currency does not change after creation. To hold another currency, open the appropriate account and use Currency Exchange.</p>
        <form method="post" class="v3-form">
            <?php echo v3CsrfInput(); ?>
            <label><span>Account nickname <small>(optional)</small></span><input type="text" name="account_alias" maxlength="100" placeholder="e.g. Everyday EUR"></label>
            <label><span>Account type</span><select name="account_type" required><option value="Personal Checking">Personal Checking</option><option value="Savings">Savings</option><option value="Current">Current</option><option value="Fixed">Fixed</option></select></label>
            <label><span>Currency</span><select name="currency" required><?php echo velmoraCurrencyOptions('USD'); ?></select></label>
            <button type="submit" name="v3_create_account" value="1" class="v3-primary-btn">Open account</button>
        </form>
    </aside>
</div>
<?php v3PageEnd(); ?>