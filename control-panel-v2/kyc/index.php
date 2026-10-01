<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT id,first_name,last_name,email,occupation,country_of_residence,status,time_uploaded FROM kyc_data ORDER BY CASE WHEN LOWER(status)='pending' THEN 0 ELSE 1 END,id DESC");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];$db->close();
cpv2Start('KYC Review','kyc');
?>
<section class="op-heading"><div><span class="op-kicker">IDENTITY OPERATIONS</span><h1>KYC review queue</h1><p>Pending identity submissions are surfaced first, followed by reviewed records.</p></div></section>
<section class="op-panel"><div class="op-search"><input id="opSearch" type="search" placeholder="Search customer, email, occupation or country"></div><div class="op-table-wrap"><table class="op-table" id="opSearchTable"><thead><tr><th>Customer</th><th>Email</th><th>Occupation</th><th>Residence</th><th>Status</th><th>Submitted</th><th></th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><strong><?php echo htmlspecialchars(trim($r['first_name'].' '.$r['last_name'])); ?></strong></td><td><?php echo htmlspecialchars($r['email']); ?></td><td><?php echo htmlspecialchars($r['occupation']?:'—'); ?></td><td><?php echo htmlspecialchars($r['country_of_residence']); ?></td><td><span class="op-status <?php echo strtolower($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td><td><?php echo htmlspecialchars($r['time_uploaded']); ?></td><td><a href="/control-panel-v2/kyc/detail/?id=<?php echo (int)$r['id']; ?>">Review</a></td></tr><?php endforeach;?>
</tbody></table></div></section><script>document.getElementById('opSearch')?.addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#opSearchTable tbody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(q)?'':'none')})</script>
<?php cpv2End(); ?>