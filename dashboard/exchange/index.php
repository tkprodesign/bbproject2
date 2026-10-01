<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$profile=v3Profile($user_email,$user_name);
$accounts=v3Accounts($user_email);
$active=array_values(array_filter($accounts,fn($a)=>$a['account_status']==='Active'));
$selectedFrom=preg_replace('/\D+/','',(string)($_GET['from']??''));
$quote=$_SESSION['v3_fx_quote']??null;
if(is_array($quote) && time()>(int)$quote['expires_at']){unset($_SESSION['v3_fx_quote']);$quote=null;}

$db=connectToDatabase();
$stmt=$db->prepare("SELECT trade_id,source_currency,target_currency,source_amount,target_amount,customer_rate,fx_spread_bps,status,executed_at
    FROM fx_trades WHERE user_email=? ORDER BY id DESC LIMIT 8");
$stmt->bind_param('s',$user_email);$stmt->execute();$history=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();$db->close();

v3PageStart('Currency Exchange','exchange',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
    <div><span class="v3-kicker">FOREIGN EXCHANGE</span><h1>Currency exchange</h1><p>Exchange funds between your own currency accounts. Velmora provides a bank quote first; you review it before execution.</p></div>
</section>

<?php if(count($active)<2): ?>
<div class="v3-alert info"><span class="material-symbols-rounded">info</span><div><strong>A second currency account is required.</strong><br>Open another currency account, then return here to request an exchange quote. <a href="/dashboard/accounts/">Open account</a></div></div>
<?php endif; ?>

<div class="v3-trade-grid">
    <section class="v3-panel">
        <div class="v3-section-head"><div><span class="v3-kicker">STEP 1</span><h2>Request bank quote</h2></div><span class="v3-step">Quote</span></div>
        <form method="post" class="v3-form">
            <?php echo v3CsrfInput(); ?>
            <label><span>Sell from account</span>
                <select name="from_account" required <?php echo count($active)<2?'disabled':''; ?>>
                    <option value="">Choose source account</option>
                    <?php foreach($active as $a): ?><option value="<?php echo htmlspecialchars($a['account_number']); ?>" <?php echo $selectedFrom===(string)$a['account_number']?'selected':''; ?>><?php echo htmlspecialchars($a['currency'].' · '.$a['account_type'].' · •••• '.substr((string)$a['account_number'],-4).' · '.velmoraFormatCurrency($a['balance'],$a['currency'])); ?></option><?php endforeach; ?>
                </select>
            </label>
            <label><span>Buy into account</span>
                <select name="to_account" required <?php echo count($active)<2?'disabled':''; ?>>
                    <option value="">Choose destination account</option>
                    <?php foreach($active as $a): ?><option value="<?php echo htmlspecialchars($a['account_number']); ?>"><?php echo htmlspecialchars($a['currency'].' · '.$a['account_type'].' · •••• '.substr((string)$a['account_number'],-4)); ?></option><?php endforeach; ?>
                </select>
            </label>
            <label><span>Amount to sell</span><input type="number" name="amount" min="0.01" step="0.01" inputmode="decimal" placeholder="0.00" required <?php echo count($active)<2?'disabled':''; ?>></label>
            <button type="submit" name="v3_quote_exchange" value="1" class="v3-primary-btn" <?php echo count($active)<2?'disabled':''; ?>>Get bank quote</button>
        </form>
        <p class="v3-form-note">A quote is valid for 60 seconds. No account is debited until you confirm the trade.</p>
    </section>

    <section class="v3-panel v3-review-card">
        <div class="v3-section-head"><div><span class="v3-kicker">STEP 2</span><h2>Review & confirm</h2></div><span class="v3-step"><?php echo $quote?'Ready':'Waiting'; ?></span></div>
        <?php if($quote): ?>
            <div class="v3-quote-main">
                <div><span>You sell</span><strong><?php echo htmlspecialchars(velmoraFormatCurrency((float)$quote['source_amount'],$quote['source_currency'])); ?></strong></div>
                <span class="material-symbols-rounded">arrow_forward</span>
                <div><span>You receive</span><strong><?php echo htmlspecialchars(velmoraFormatCurrency((float)$quote['target_amount'],$quote['target_currency'])); ?></strong></div>
            </div>
            <dl class="v3-quote-details">
                <div><dt>Bank rate</dt><dd>1 <?php echo htmlspecialchars($quote['source_currency']); ?> = <?php echo htmlspecialchars(number_format((float)$quote['customer_rate'],6)); ?> <?php echo htmlspecialchars($quote['target_currency']); ?></dd></div>
                <div><dt>FX margin</dt><dd><?php echo htmlspecialchars(number_format(((int)$quote['spread_bps'])/100,2)); ?>%</dd></div>
                <div><dt>Quote reference</dt><dd><?php echo htmlspecialchars($quote['quote_id']); ?></dd></div>
                <div><dt>Expires</dt><dd><?php echo htmlspecialchars(date('H:i:s',(int)$quote['expires_at'])); ?></dd></div>
            </dl>
            <form method="post">
                <?php echo v3CsrfInput(); ?><input type="hidden" name="quote_id" value="<?php echo htmlspecialchars($quote['quote_id']); ?>">
                <button type="submit" name="v3_execute_exchange" value="1" class="v3-primary-btn full">Confirm exchange</button>
            </form>
            <p class="v3-form-note">On confirmation, the source account is debited and the destination currency account is credited as one recorded FX trade.</p>
        <?php else: ?>
            <div class="v3-review-placeholder"><span class="material-symbols-rounded">request_quote</span><strong>No active quote</strong><p>Choose two accounts in different currencies and request a quote. The trade terms will appear here for review.</p></div>
        <?php endif; ?>
    </section>
</div>

<section class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">TRADE HISTORY</span><h2>Recent exchanges</h2></div></div>
    <div class="v3-table-wrap">
        <table class="v3-table">
            <thead><tr><th>Date</th><th>Reference</th><th>Sold</th><th>Bought</th><th>Rate</th><th>Status</th></tr></thead>
            <tbody>
            <?php foreach($history as $h): ?>
                <tr><td><?php echo $h['executed_at']?htmlspecialchars(date('M d, Y H:i',(int)$h['executed_at'])):'—'; ?></td>
                <td><?php echo htmlspecialchars($h['trade_id']); ?></td>
                <td><?php echo htmlspecialchars(velmoraFormatCurrency((float)$h['source_amount'],$h['source_currency'])); ?></td>
                <td><?php echo htmlspecialchars(velmoraFormatCurrency((float)$h['target_amount'],$h['target_currency'])); ?></td>
                <td><?php echo htmlspecialchars(number_format((float)$h['customer_rate'],6)); ?></td>
                <td><span class="v3-status successful"><?php echo htmlspecialchars($h['status']); ?></span></td></tr>
            <?php endforeach; ?>
            <?php if(empty($history)): ?><tr><td colspan="6"><div class="v3-empty">No currency trades have been executed yet.</div></td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</section>
<?php v3PageEnd(); ?>