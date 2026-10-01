<?php
require_once __DIR__ . '/_app.php';
require_once __DIR__ . '/_layout.php';
$db=connectToDatabase();
$metrics=[];
foreach([
 'customers'=>"SELECT COUNT(*) FROM users",
 'accounts'=>"SELECT COUNT(*) FROM accounts",
 'pending_transfers'=>"SELECT COUNT(*) FROM transactions WHERE type='Transfer' AND LOWER(status)='pending'",
 'pending_kyc'=>"SELECT COUNT(*) FROM kyc_data WHERE LOWER(status)='pending'",
 'open_support'=>"SELECT COUNT(*) FROM support_cases WHERE status IN ('Open','In Review')"
] as $k=>$sql){$res=$db->query($sql);$metrics[$k]=(int)($res?$res->fetch_row()[0]:0);}
$recentTx=$db->query("SELECT transaction_id,user_email,type,amount,currency,status,time FROM transactions ORDER BY time DESC LIMIT 8");
$recentKyc=$db->query("SELECT id,first_name,last_name,email,status,time_uploaded FROM kyc_data ORDER BY id DESC LIMIT 6");
$db->close();
cpv2Start('Operations Overview','overview');
?>
<section class="op-heading"><div><span class="op-kicker">BANK OPERATIONS</span><h1>Operations overview</h1><p>A working view of customers, accounts, money movement and identity-review workload.</p></div></section>
<div class="op-grid">
 <article class="op-stat"><span>Customers</span><strong><?php echo $metrics['customers']; ?></strong></article>
 <article class="op-stat"><span>Accounts</span><strong><?php echo $metrics['accounts']; ?></strong></article>
 <article class="op-stat"><span>Pending transfers</span><strong><?php echo $metrics['pending_transfers']; ?></strong></article>
 <article class="op-stat"><span>Pending KYC reviews</span><strong><?php echo $metrics['pending_kyc']; ?></strong></article>
 <article class="op-stat"><span>Open support cases</span><strong><?php echo $metrics['open_support']; ?></strong></article>
</div>
<div class="op-two">
<section class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">LEDGER</span><h2>Recent transactions</h2></div><a href="/master-control-panel/transactions/">View all</a></div>
<div class="op-table-wrap"><table class="op-table"><thead><tr><th>Reference</th><th>Customer</th><th>Type</th><th>Status</th><th>Amount</th></tr></thead><tbody>
<?php if($recentTx):while($r=$recentTx->fetch_assoc()):?><tr><td><?php echo htmlspecialchars($r['transaction_id']); ?></td><td><?php echo htmlspecialchars($r['user_email']); ?></td><td><?php echo htmlspecialchars($r['type']); ?></td><td><span class="op-status <?php echo htmlspecialchars(strtolower($r['status'])); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td><td><?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['amount'],$r['currency'])); ?></td></tr><?php endwhile;endif;?>
</tbody></table></div></section>
<aside class="op-panel"><div class="op-panel-head"><div><span class="op-kicker">IDENTITY</span><h2>Recent KYC</h2></div><a href="/master-control-panel/kyc/">Review queue</a></div>
<div class="op-list"><?php if($recentKyc):while($r=$recentKyc->fetch_assoc()):?><article><span class="material-symbols-rounded">badge</span><div><strong><?php echo htmlspecialchars(trim($r['first_name'].' '.$r['last_name'])); ?></strong><small><?php echo htmlspecialchars($r['email']); ?> · <?php echo htmlspecialchars($r['status']); ?></small></div><a class="op-btn secondary" href="/master-control-panel/kyc/detail/?id=<?php echo (int)$r['id']; ?>">Open</a></article><?php endwhile;endif;?></div></aside>
</div>
<?php cpv2End(); ?>