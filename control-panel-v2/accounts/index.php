<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT a.id,a.account_number,a.account_type,a.account_alias,a.user_name,a.user_email,a.currency,a.account_status,a.opened_at,a.creation_time,
 COALESCE(SUM(CASE WHEN t.status IS NULL OR LOWER(t.status)<>'failed' THEN t.amount ELSE 0 END),0) balance
 FROM accounts a LEFT JOIN transactions t ON t.account_number=a.account_number
 GROUP BY a.id,a.account_number,a.account_type,a.account_alias,a.user_name,a.user_email,a.currency,a.account_status,a.opened_at,a.creation_time ORDER BY a.id DESC");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];$db->close();$selected=preg_replace('/\D+/','',(string)($_GET['account']??''));
cpv2Start('Accounts','accounts');
?>
<section class="op-heading"><div><span class="op-kicker">ACCOUNTS</span><h1>Account registry</h1><p>Currency, balance, account status and owner relationship in one operational view.</p></div></section>
<section class="op-panel"><div class="op-search"><input id="opSearch" type="search" placeholder="Search account, customer or currency"></div><div class="op-table-wrap"><table class="op-table" id="opSearchTable"><thead><tr><th>Account</th><th>Customer</th><th>Currency</th><th>Balance</th><th>Status</th><th>Opened</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr<?php echo $selected===(string)$r['account_number']?' style="background:#f0f6fa"':''; ?>><td><strong><?php echo htmlspecialchars($r['account_alias']?:$r['account_type']); ?></strong><small><?php echo htmlspecialchars($r['account_number']); ?></small></td><td><?php echo htmlspecialchars($r['user_name']); ?><small><?php echo htmlspecialchars($r['user_email']); ?></small></td><td><?php echo htmlspecialchars($r['currency']); ?></td><td><?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['balance'],$r['currency'])); ?></td><td><span class="op-status <?php echo strtolower($r['account_status']); ?>"><?php echo htmlspecialchars($r['account_status']); ?></span></td><td><?php echo !empty($r['opened_at'])?htmlspecialchars(date('M d, Y',strtotime($r['opened_at']))):htmlspecialchars(date('M d, Y',(int)$r['creation_time'])); ?></td></tr><?php endforeach;?>
</tbody></table></div></section><script>document.getElementById('opSearch')?.addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#opSearchTable tbody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(q)?'':'none')})</script>
<?php cpv2End(); ?>