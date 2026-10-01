<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT id,transaction_id,type,user_email,account_number,amount,currency,status,time,channel,value_date,posted_at,to_bank_name,counter_currency,counter_amount,fx_rate FROM transactions ORDER BY time DESC,id DESC");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];$db->close();
cpv2Start('Transactions','transactions');
?>
<section class="op-heading"><div><span class="op-kicker">LEDGER</span><h1>Transactions</h1><p>Operational view of ledger entries, references, channels, status and countervalue information.</p></div></section>
<section class="op-panel"><div class="op-search"><input id="opSearch" type="search" placeholder="Search reference, email, account or description"></div><div class="op-table-wrap"><table class="op-table" id="opSearchTable"><thead><tr><th>Reference</th><th>Customer</th><th>Type</th><th>Account</th><th>Channel</th><th>Status</th><th>Amount</th><th>Value date</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><strong><?php echo htmlspecialchars($r['transaction_id']); ?></strong><?php if(!empty($r['to_bank_name'])):?><small><?php echo htmlspecialchars($r['to_bank_name']); ?></small><?php endif;?></td><td><?php echo htmlspecialchars($r['user_email']); ?></td><td><?php echo htmlspecialchars($r['type']); ?></td><td><?php echo htmlspecialchars($r['account_number']); ?></td><td><?php echo htmlspecialchars($r['channel']?:'Online Banking'); ?></td><td><span class="op-status <?php echo htmlspecialchars(strtolower($r['status'])); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td><td><?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['amount'],$r['currency'])); ?><?php if(!empty($r['counter_currency'])&&$r['counter_amount']!==null):?><small>Countervalue <?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['counter_amount'],$r['counter_currency'])); ?></small><?php endif;?></td><td><?php echo htmlspecialchars($r['value_date']?:date('Y-m-d',(int)$r['time'])); ?></td></tr><?php endforeach;?>
</tbody></table></div></section>
<script>document.getElementById('opSearch')?.addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#opSearchTable tbody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(q)?'':'none')})</script>
<?php cpv2End(); ?>