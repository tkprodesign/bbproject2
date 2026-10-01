<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';

$db=connectToDatabase();
$res=$db->query("SELECT id,case_number,user_email,category,subject,status,priority,related_transaction_id,assigned_to,last_customer_message_at,last_operator_message_at,created_at,updated_at
    FROM support_cases ORDER BY CASE WHEN status='Open' THEN 0 WHEN status='In Review' THEN 1 WHEN status='Resolved' THEN 2 ELSE 3 END, updated_at DESC, id DESC");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];
$db->close();

cpv2Start('Support Cases','support');
?>
<section class="op-heading">
  <div><span class="op-kicker">CUSTOMER SUPPORT</span><h1>Support case queue</h1><p>Authenticated customer cases, conversation state, priority and assignment in one operations queue.</p></div>
</section>

<section class="op-panel">
  <div class="op-search"><input id="opSearch" type="search" placeholder="Search case, customer, subject, category or status"></div>
  <div class="op-table-wrap">
    <table class="op-table" id="opSearchTable">
      <thead><tr><th>Case</th><th>Customer</th><th>Category</th><th>Priority</th><th>Status</th><th>Assigned</th><th>Updated</th><th></th></tr></thead>
      <tbody>
      <?php foreach($rows as $r): $statusClass=strtolower(str_replace(' ','-',(string)$r['status'])); ?>
        <tr>
          <td><strong><?php echo htmlspecialchars($r['case_number']); ?></strong><small><?php echo htmlspecialchars($r['subject']); ?></small></td>
          <td><?php echo htmlspecialchars($r['user_email']); ?></td>
          <td><?php echo htmlspecialchars($r['category']); ?></td>
          <td><?php echo htmlspecialchars($r['priority']); ?></td>
          <td><span class="op-status <?php echo htmlspecialchars($statusClass); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td>
          <td><?php echo htmlspecialchars($r['assigned_to']?:'Unassigned'); ?></td>
          <td><?php echo htmlspecialchars(date('M d, Y H:i',strtotime((string)$r['updated_at']))); ?></td>
          <td><a href="/control-panel/support-cases/detail/?id=<?php echo (int)$r['id']; ?>">Open</a></td>
        </tr>
      <?php endforeach; ?>
      <?php if(empty($rows)): ?><tr><td colspan="8">No support cases found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</section>
<script>document.getElementById('opSearch')?.addEventListener('input',function(){const q=this.value.toLowerCase();document.querySelectorAll('#opSearchTable tbody tr').forEach(r=>r.style.display=r.textContent.toLowerCase().includes(q)?'':'none')})</script>
<?php cpv2End(); ?>