<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$profile=v3Profile($user_email,$user_name);
$accounts=array_values(array_filter(v3Accounts($user_email),fn($a)=>$a['account_status']==='Active'));
$beneficiaries=v3Beneficiaries($user_email,true);
$selectedFrom=preg_replace('/\D+/','',(string)($_GET['from']??''));
$selectedBeneficiaryId=(int)($_GET['beneficiary']??0);
$selectedBeneficiary=null;
foreach($beneficiaries as $candidate){if((int)$candidate['id']===$selectedBeneficiaryId){$selectedBeneficiary=$candidate;break;}}
$quote=$_SESSION['v3_transfer_quote']??null;
if(is_array($quote)&&time()>(int)$quote['expires_at']){unset($_SESSION['v3_transfer_quote']);$quote=null;}
v3PageStart('Transfer Funds','transfer',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
    <div><span class="v3-kicker">PAYMENTS</span><h1>Transfer funds</h1><p>Prepare the transfer, review any currency conversion and bank rate, then submit it for processing.</p></div>
</section>
<div class="v3-trade-grid">
<section class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">STEP 1</span><h2>Transfer details</h2></div><span class="v3-step">Prepare</span></div>
    <form method="post" class="v3-form">
        <?php echo v3CsrfInput(); ?>
        <label><span>From account</span><select name="from_account" required><option value="">Choose account</option><?php foreach($accounts as $a): ?><option value="<?php echo htmlspecialchars($a['account_number']); ?>" <?php echo $selectedFrom===(string)$a['account_number']?'selected':''; ?>><?php echo htmlspecialchars($a['currency'].' · •••• '.substr((string)$a['account_number'],-4).' · '.velmoraFormatCurrency($a['balance'],$a['currency'])); ?></option><?php endforeach; ?></select></label>
        <?php if(!empty($beneficiaries)): ?><label><span>Saved beneficiary</span><select onchange="if(this.value)window.location='/dashboard/transfer/?beneficiary='+encodeURIComponent(this.value)+'<?php echo $selectedFrom!==''?'&from='.urlencode($selectedFrom):''; ?>'"><option value="">Enter recipient manually</option><?php foreach($beneficiaries as $b): ?><option value="<?php echo (int)$b['id']; ?>" <?php echo $selectedBeneficiaryId===(int)$b['id']?'selected':''; ?>><?php echo htmlspecialchars($b['beneficiary_name'].' · '.$b['bank_name'].' · •••• '.substr((string)$b['account_number'],-4)); ?></option><?php endforeach; ?></select></label><?php endif; ?>
        <label><span>Recipient / account name</span><input type="text" name="recipient_name" required value="<?php echo htmlspecialchars($selectedBeneficiary['beneficiary_name']??''); ?>"></label>
        <div class="v3-form-row"><label><span>Recipient bank</span><input type="text" name="bank_name" value="<?php echo htmlspecialchars($selectedBeneficiary['bank_name']??''); ?>" placeholder="Optional if not specified"></label><label><span>Recipient account</span><input type="text" name="account_number" required value="<?php echo htmlspecialchars($selectedBeneficiary['account_number']??''); ?>"></label></div>
        <div class="v3-form-row"><label><span>Account type</span><select name="account_type"><?php foreach(['Savings','Current','Not Sure'] as $type): ?><option value="<?php echo $type; ?>" <?php echo (($selectedBeneficiary['account_type']??'')===$type)?'selected':''; ?>><?php echo $type; ?></option><?php endforeach; ?></select></label><label><span>Recipient currency</span><select name="currency" required><?php echo velmoraCurrencyOptions($selectedBeneficiary['currency']??'USD'); ?></select></label></div>
        <label><span>Amount to debit from your account</span><input type="number" min="0.01" step="0.01" name="amount" required></label>
        <button type="submit" name="v3_quote_transfer" value="1" class="v3-primary-btn">Review transfer</button>
    </form>
</section>
<section class="v3-panel v3-review-card">
    <div class="v3-section-head"><div><span class="v3-kicker">STEP 2</span><h2>Review & submit</h2></div><span class="v3-step"><?php echo $quote?'Ready':'Waiting'; ?></span></div>
    <?php if($quote): ?>
        <div class="v3-transfer-summary">
            <div><span>Recipient</span><strong><?php echo htmlspecialchars($quote['recipient_name']); ?></strong><small><?php echo htmlspecialchars(($quote['bank_name']!==''?$quote['bank_name'].' · ':'').$quote['recipient_account']); ?></small></div>
            <div><span>You send</span><strong><?php echo htmlspecialchars(velmoraFormatCurrency((float)$quote['amount'],$quote['source_currency'])); ?></strong></div>
            <div><span>Recipient receives</span><strong><?php echo htmlspecialchars(velmoraFormatCurrency((float)$quote['recipient_amount'],$quote['recipient_currency'])); ?></strong></div>
        </div>
        <dl class="v3-quote-details">
            <div><dt>Bank rate</dt><dd>1 <?php echo htmlspecialchars($quote['source_currency']); ?> = <?php echo htmlspecialchars(number_format((float)$quote['customer_rate'],6)); ?> <?php echo htmlspecialchars($quote['recipient_currency']); ?></dd></div>
            <div><dt>FX margin</dt><dd><?php echo htmlspecialchars(number_format(((int)$quote['spread_bps'])/100,2)); ?>%</dd></div>
            <div><dt>Quote reference</dt><dd><?php echo htmlspecialchars($quote['quote_id']); ?></dd></div>
        </dl>
        <form method="post"><?php echo v3CsrfInput(); ?><input type="hidden" name="quote_id" value="<?php echo htmlspecialchars($quote['quote_id']); ?>"><button class="v3-primary-btn full" type="submit" name="v3_execute_transfer" value="1">Submit transfer</button></form>
        <p class="v3-form-note">Submitting creates a pending bank transfer. It remains visible in your ledger while processing.</p>
    <?php else: ?>
        <div class="v3-review-placeholder"><span class="material-symbols-rounded">payments</span><strong>No transfer prepared</strong><p>Enter the payment details first. Any FX conversion will be shown here before submission.</p></div>
    <?php endif; ?>
</section>
</div>
<?php v3PageEnd(); ?>