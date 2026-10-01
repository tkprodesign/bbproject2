<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT id,user_email,event_type,description,ip_address,user_agent,created_at FROM security_events ORDER BY id DESC LIMIT 200");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];$db->close();
cpv2Start('Security & Audit','audit');
?>
<section class="op-heading"><div><span class="op-kicker">SECURITY &amp; AUDIT</span><h1>Security event log</h1><p>Recorded registration, sign-in, KYC and preference activity available for operational review.</p></div></section>
<section class="op-panel"><div class="op-search"><input id="opSearch" type="search" placeholder="Search email, event, IP or description"></div>
<div class="op-table-wrap"><table class="op-table" id="opSearchTable"><thead><tr><th>Time</th><th>Customer</th><th>Event</th><th>Description</th><th>IP address</th><th>User agent</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><?php echo htmlspecialchars(date('M d, Y H:i:s',strtotime($r['created_at']))); ?></td><td><?php echo htmlspecialchars($r['user_email']); ?></td><td><strong><?php echo htmlspecialchars($r['event_type']); ?></strong></td><td><?php echo htmlspecialchars($r['description']); ?></td><td><?php echo htmlspecialchars($r['ip_address']?:'—'); ?></td><td><small><?php echo htmlspecialchars(mb_strimwidth((string)$r['user_agent'],0,80,'…')); ?></small></td></tr><?php endforeach;?>
<?php if(empty($rows)):?><tr><td colspan="6">No security events recorded.</td></tr><?php endif;?>
</tbody></table></div></section>
<script>document.getElementById('opSearch')?.addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#opSearchTable tbody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(q)?'':'none')})</script>
<?php cpv2End(); ?>