<?php
require_once __DIR__ . '/../../_app.php';
require_once __DIR__ . '/../../_layout.php';

$id=(int)($_GET['id']??0);
$db=connectToDatabase();
$stmt=$db->prepare("SELECT id,case_number,user_email,category,subject,status,priority,related_transaction_id,assigned_to,resolved_at,created_at,updated_at FROM support_cases WHERE id=? LIMIT 1");
$stmt->bind_param('i',$id);$stmt->execute();$case=$stmt->get_result()->fetch_assoc()?:null;$stmt->close();
if(!$case){$db->close();cpv2Go('/master-control-panel/support-cases/');}
$stmt=$db->prepare("SELECT id,sender_role,sender_email,message,created_at FROM support_case_messages WHERE case_id=? ORDER BY id ASC");
$stmt->bind_param('i',$id);$stmt->execute();$messages=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();
$db->close();

cpv2Start('Support Case','support');
?>
<section class="op-heading">
  <div><span class="op-kicker">SUPPORT CASE</span><h1><?php echo htmlspecialchars($case['case_number']); ?></h1><p><?php echo htmlspecialchars($case['user_email']); ?></p></div>
  <a class="op-btn secondary" href="/master-control-panel/support-cases/">Back to queue</a>
</section>

<div class="op-two">
  <section class="op-panel">
    <div class="op-case-head">
      <div>
        <span class="op-kicker"><?php echo htmlspecialchars($case['category']); ?></span>
        <h1><?php echo htmlspecialchars($case['subject']); ?></h1>
        <div class="op-case-meta">
          <span><?php echo htmlspecialchars($case['status']); ?></span>
          <span><?php echo htmlspecialchars($case['priority']); ?> priority</span>
          <?php if(!empty($case['related_transaction_id'])): ?><span>Transaction: <?php echo htmlspecialchars($case['related_transaction_id']); ?></span><?php endif; ?>
          <span>Opened <?php echo htmlspecialchars(date('M d, Y H:i',strtotime((string)$case['created_at']))); ?></span>
        </div>
      </div>
    </div>

    <div class="op-thread">
      <?php foreach($messages as $message): $operator=strtolower((string)$message['sender_role'])==='operator'; ?>
        <article class="op-message <?php echo $operator?'operator':'customer'; ?>">
          <strong><?php echo $operator?'Velmora Operations':'Customer'; ?></strong>
          <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
          <small><?php echo htmlspecialchars($message['sender_email'].' · '.date('M d, Y H:i',strtotime((string)$message['created_at']))); ?></small>
        </article>
      <?php endforeach; ?>
    </div>

    <form method="post" class="op-form" style="margin-top:18px">
      <?php echo cpv2CsrfInput(); ?>
      <input type="hidden" name="case_id" value="<?php echo (int)$case['id']; ?>">
      <label><span>Reply to customer</span><textarea name="message" required placeholder="Write a clear response or request the information needed to continue."></textarea></label>
      <button class="op-btn primary" type="submit" name="cpv2_support_reply" value="1">Send reply</button>
    </form>
  </section>

  <aside class="op-panel">
    <div class="op-panel-head"><div><span class="op-kicker">CASE CONTROL</span><h2>Status & assignment</h2></div></div>
    <dl class="op-detail">
      <div><dt>Customer</dt><dd><?php echo htmlspecialchars($case['user_email']); ?></dd></div>
      <div><dt>Assigned to</dt><dd><?php echo htmlspecialchars($case['assigned_to']?:'Unassigned'); ?></dd></div>
      <div><dt>Priority</dt><dd><?php echo htmlspecialchars($case['priority']); ?></dd></div>
      <div><dt>Updated</dt><dd><?php echo htmlspecialchars(date('M d, Y H:i',strtotime((string)$case['updated_at']))); ?></dd></div>
    </dl>
    <form method="post" class="op-form" style="margin-top:16px">
      <?php echo cpv2CsrfInput(); ?>
      <input type="hidden" name="case_id" value="<?php echo (int)$case['id']; ?>">
      <label><span>Status</span>
        <select name="status">
          <?php foreach(['Open','In Review','Resolved','Closed'] as $status): ?><option value="<?php echo $status; ?>" <?php echo $case['status']===$status?'selected':''; ?>><?php echo $status; ?></option><?php endforeach; ?>
        </select>
      </label>
      <button class="op-btn secondary" type="submit" name="cpv2_support_status" value="1">Update case status</button>
    </form>
    <div class="op-panel-head" style="margin-top:24px"><div><span class="op-kicker">CUSTOMER RECORD</span><h2>Related actions</h2></div></div>
    <div class="op-list">
      <article><span class="material-symbols-rounded">person</span><div><strong>Customer directory</strong><small>Locate the customer relationship and accounts.</small></div><a href="/master-control-panel/customers/?q=<?php echo urlencode($case['user_email']); ?>">Open</a></article>
      <?php if(!empty($case['related_transaction_id'])): ?><article><span class="material-symbols-rounded">receipt_long</span><div><strong>Related transaction</strong><small><?php echo htmlspecialchars($case['related_transaction_id']); ?></small></div><a href="/master-control-panel/transactions/">Ledger</a></article><?php endif; ?>
    </div>
  </aside>
</div>
<?php cpv2End(); ?>