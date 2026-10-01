<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT u.id,u.customer_number,u.name,u.email,u.user_status,u.kyc_level,u.date_registered,u.last_login_at,
 (SELECT COUNT(*) FROM accounts a WHERE a.user_email=u.email) account_count,
 (SELECT status FROM kyc_data k WHERE k.email=u.email ORDER BY k.id DESC LIMIT 1) kyc_status
 FROM users u ORDER BY u.date_registered DESC");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];$db->close();
cpv2Start('Customers','customers');
?>
<section class="op-heading"><div><span class="op-kicker">CUSTOMERS</span><h1>Customer directory</h1><p>One view of client identity, relationship status, KYC state and account count.</p></div></section>
<section class="op-panel">
<div class="op-search"><input id="opSearch" type="search" placeholder="Search name, email or customer number"></div>
<div class="op-table-wrap"><table class="op-table" id="opSearchTable"><thead><tr><th>Customer</th><th>Email</th><th>Relationship</th><th>KYC</th><th>Accounts</th><th>Registered</th><th>Last login</th><th></th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><strong><?php echo htmlspecialchars($r['name']); ?></strong><small><?php echo htmlspecialchars($r['customer_number']?:'Not assigned'); ?></small></td><td><?php echo htmlspecialchars($r['email']); ?></td><td><span class="op-status <?php echo strtolower($r['user_status']?:'active'); ?>"><?php echo htmlspecialchars($r['user_status']?:'Active'); ?></span></td><td><?php echo htmlspecialchars($r['kyc_status']?:'Not submitted'); ?></td><td><?php echo (int)$r['account_count']; ?></td><td><?php echo htmlspecialchars(date('M d, Y',(int)$r['date_registered'])); ?></td><td><?php echo $r['last_login_at']?htmlspecialchars(date('M d, Y H:i',strtotime($r['last_login_at']))):'—'; ?></td><td><a href="/control-panel-v2/customers/detail/?id=<?php echo (int)$r['id']; ?>">Open</a></td></tr><?php endforeach;?>
</tbody></table></div></section>
<script>document.getElementById('opSearch')?.addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#opSearchTable tbody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(q)?'':'none')})</script>
<?php cpv2End(); ?>