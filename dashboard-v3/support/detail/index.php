<?php
require_once __DIR__ . '/../../_app.php';
require_once __DIR__ . '/../../_layout.php';

$profile=v3Profile($user_email,$user_name);
$id=(int)($_GET['id']??0);
$case=v3SupportCase($user_email,$id);
if(!$case)v3Redirect('/dashboard-v3/support/');

v3PageStart('Support Case','support',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
  <div><span class="v3-kicker">SUPPORT CASE</span><h1><?php echo htmlspecialchars($case['case_number']); ?></h1><p>Authenticated conversation with Velmora support.</p></div>
  <a class="v3-secondary-btn" href="/dashboard-v3/support/">Back to support</a>
</section>

<section class="v3-panel">
  <div class="v3-case-head">
    <div>
      <span class="v3-kicker"><?php echo htmlspecialchars($case['category']); ?></span>
      <h1><?php echo htmlspecialchars($case['subject']); ?></h1>
      <div class="v3-case-meta">
        <span>Status: <?php echo htmlspecialchars($case['status']); ?></span>
        <span><?php echo htmlspecialchars($case['priority']); ?> priority</span>
        <?php if(!empty($case['related_transaction_id'])): ?><span>Transaction: <?php echo htmlspecialchars($case['related_transaction_id']); ?></span><?php endif; ?>
        <span>Opened <?php echo htmlspecialchars(date('M d, Y H:i',strtotime((string)$case['created_at']))); ?></span>
      </div>
    </div>
  </div>

  <div class="v3-thread">
    <?php foreach($case['messages'] as $message): $customer=strtolower((string)$message['sender_role'])==='customer'; ?>
      <article class="v3-message <?php echo $customer?'customer':'operator'; ?>">
        <strong><?php echo $customer?'You':'Velmora Support'; ?></strong>
        <p><?php echo nl2br(htmlspecialchars($message['message'])); ?></p>
        <small><?php echo htmlspecialchars(date('M d, Y H:i',strtotime((string)$message['created_at']))); ?></small>
      </article>
    <?php endforeach; ?>
  </div>

  <form method="post" class="v3-form" style="margin-top:20px">
    <?php echo v3CsrfInput(); ?>
    <input type="hidden" name="case_id" value="<?php echo (int)$case['id']; ?>">
    <label><span>Reply to support</span><textarea name="message" required placeholder="Add more context or respond to the bank."></textarea></label>
    <button class="v3-primary-btn" type="submit" name="v3_reply_support_case" value="1">Send reply</button>
  </form>
</section>
<?php v3PageEnd(); ?>