<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT transaction_id,user_email,account_number,type,amount,currency,description,status,posted_at,time FROM transactions WHERE channel='Operations Console' ORDER BY time DESC,id DESC LIMIT 50");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];$db->close();
cpv2Start('Ledger Adjustments','adjustments');
?>
<section class="op-heading"><div><span class="op-kicker">CONTROLLED LEDGER ACTIONS</span><h1>Ledger adjustments</h1><p>Post an explicit operations credit or debit in the account’s own currency. Every adjustment creates a transaction reference, customer notification and security event.</p></div></section>
<div class="op-two">
<section class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">POST ADJUSTMENT</span><h2>Credit or debit account</h2></div></div>
<form method="post" class="op-form"><?php echo cpv2CsrfInput(); ?>
<label><span>Customer email</span><input type="email" name="email" required></label>
<label><span>Account number</span><input type="text" inputmode="numeric" name="account_number" required></label>
<div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
<label><span>Direction</span><select name="direction" required><option value="Credit">Credit</option><option value="Debit">Debit</option></select></label>
<label><span>Amount in account currency</span><input type="number" min="0.01" step="0.01" name="amount" required></label>
</div>
<label><span>Description / reason</span><textarea name="description" required placeholder="Explain why this operations adjustment is being posted."></textarea></label>
<button class="op-btn primary" type="submit" name="cpv2_adjust_ledger" value="1">Post adjustment</button>
</form>
</section>
<aside class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">CONTROL PRINCIPLE</span><h2>Adjustments are ledger entries.</h2></div></div>
<div class="op-list">
<article><span class="material-symbols-rounded">currency_exchange</span><div><strong>No hidden currency conversion</strong><small>Enter the adjustment in the denomination of the selected account. Use the customer FX workflow for an actual currency trade.</small></div></article>
<article><span class="material-symbols-rounded">receipt_long</span><div><strong>Reference created</strong><small>Every adjustment is written to the transaction ledger with an OPS reference.</small></div></article>
<article><span class="material-symbols-rounded">notifications</span><div><strong>Customer notice</strong><small>The customer receives an in-app notification when enabled.</small></div></article>
</div></aside>
</div>
<section class="op-panel" style="margin-top:18px"><div class="op-panel-head"><div><span class="op-kicker">RECENT ADJUSTMENTS</span><h2>Operations entries</h2></div></div>
<div class="op-table-wrap"><table class="op-table"><thead><tr><th>Reference</th><th>Customer</th><th>Account</th><th>Type</th><th>Amount</th><th>Status</th><th>Posted</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><?php echo htmlspecialchars($r['transaction_id']); ?></td><td><?php echo htmlspecialchars($r['user_email']); ?></td><td><?php echo htmlspecialchars($r['account_number']); ?></td><td><?php echo htmlspecialchars($r['type']); ?><small><?php echo htmlspecialchars($r['description']); ?></small></td><td><?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['amount'],$r['currency'])); ?></td><td><?php echo htmlspecialchars($r['status']); ?></td><td><?php echo htmlspecialchars($r['posted_at']?:date('Y-m-d H:i:s',(int)$r['time'])); ?></td></tr><?php endforeach;?>
<?php if(empty($rows)):?><tr><td colspan="7">No operations adjustments have been posted.</td></tr><?php endif;?>
</tbody></table></div></section>
<?php cpv2End(); ?>