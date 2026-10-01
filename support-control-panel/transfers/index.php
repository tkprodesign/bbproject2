<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT id,transaction_id,user_email,account_number,amount,currency,status,time,to_bank_name,recipient_name,to_account_type,to_account_number,counter_currency,counter_amount,fx_rate FROM transactions WHERE type='Transfer' ORDER BY time DESC,id DESC");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];$db->close();
cpv2Start('Transfer Review','transfers');
?>
<section class="op-heading"><div><span class="op-kicker">PAYMENT OPERATIONS</span><h1>Transfer review</h1><p>Review pending and completed transfer records, receiving details and any associated FX countervalue.</p></div></section>
<section class="op-panel"><div class="op-table-wrap"><table class="op-table"><thead><tr><th>Reference</th><th>Customer</th><th>From</th><th>Recipient</th><th>Amount</th><th>Status</th><th>Action</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><strong><?php echo htmlspecialchars($r['transaction_id']); ?></strong><small><?php echo htmlspecialchars(date('M d, Y H:i',(int)$r['time'])); ?></small></td><td><?php echo htmlspecialchars($r['user_email']); ?></td><td>•••• <?php echo htmlspecialchars(substr((string)$r['account_number'],-4)); ?></td><td><strong><?php echo htmlspecialchars($r['recipient_name']?:'—'); ?></strong><small><?php echo htmlspecialchars((($r['to_bank_name']??'')!==''?$r['to_bank_name'].' · ':'').($r['to_account_number']?:'').' · '.($r['counter_currency']?:$r['currency'])); ?></small></td><td><?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['amount'],$r['currency'])); ?><?php if($r['counter_amount']!==null&&$r['counter_currency']):?><small>Receives <?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['counter_amount'],$r['counter_currency'])); ?></small><?php endif;?></td><td><span class="op-status <?php echo strtolower($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td><td>
<?php if(strtolower($r['status'])==='pending'): ?><form method="post" style="display:flex;gap:6px"><?php echo cpv2CsrfInput(); ?><input type="hidden" name="transaction_id" value="<?php echo (int)$r['id']; ?>"><button class="op-btn primary" name="cpv2_transfer_decision" value="1" type="submit" onclick="this.form.decision.value='Successful'">Approve</button><button class="op-btn danger" name="cpv2_transfer_decision" value="1" type="submit" onclick="this.form.decision.value='Failed'">Reject</button><input type="hidden" name="decision" value=""></form><?php else: ?>—<?php endif; ?>
</td></tr><?php endforeach;?>
<?php if(empty($rows)):?><tr><td colspan="7">No transfers found.</td></tr><?php endif;?>
</tbody></table></div></section>
<?php cpv2End(); ?>