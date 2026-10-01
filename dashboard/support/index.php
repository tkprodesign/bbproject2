<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';

$profile=v3Profile($user_email,$user_name);
$cases=v3SupportCases($user_email);

v3PageStart('Support Messages','support',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
  <div><span class="v3-kicker">AUTHENTICATED SUPPORT</span><h1>Support messages</h1><p>Open a banking support case from inside your signed-in workspace and keep the conversation attached to a traceable case number.</p></div>
</section>

<div class="v3-support-grid">
  <section class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">YOUR CASES</span><h2><?php echo count($cases); ?> support case<?php echo count($cases)===1?'':'s'; ?></h2></div></div>
    <?php foreach($cases as $case): ?>
      <article class="v3-support-case">
        <span class="v3-list-icon"><span class="material-symbols-rounded">support_agent</span></span>
        <div>
          <strong><?php echo htmlspecialchars($case['subject']); ?></strong>
          <small><?php echo htmlspecialchars($case['case_number'].' · '.$case['category'].' · '.$case['priority'].' priority'); ?><br>Updated <?php echo htmlspecialchars(date('M d, Y H:i',strtotime((string)$case['updated_at']))); ?></small>
        </div>
        <a href="/dashboard/support/detail/?id=<?php echo (int)$case['id']; ?>">Open case</a>
      </article>
    <?php endforeach; ?>
    <?php if(empty($cases)): ?><div class="v3-empty">You have not opened any support cases yet.</div><?php endif; ?>
  </section>

  <aside class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">NEW CASE</span><h2>Contact bank support</h2></div></div>
    <p class="v3-form-note">Use this authenticated channel for account-specific questions. Do not send passwords or one-time security codes.</p>
    <form method="post" class="v3-form">
      <?php echo v3CsrfInput(); ?>
      <label><span>Category</span>
        <select name="category" required>
          <?php foreach(['Accounts','Transfers','FX','Cards','Loans','Profile & KYC','Security','General'] as $cat): ?><option><?php echo htmlspecialchars($cat); ?></option><?php endforeach; ?>
        </select>
      </label>
      <label><span>Priority</span><select name="priority"><option>Normal</option><option>Urgent</option></select></label>
      <label><span>Related transaction reference <small>(optional)</small></span><input type="text" name="related_transaction_id" maxlength="40" placeholder="e.g. TX-..."></label>
      <label><span>Subject</span><input type="text" name="subject" maxlength="190" required placeholder="Briefly describe the issue"></label>
      <label><span>Message</span><textarea name="message" required placeholder="Explain what happened, what you expected, and any useful account or transaction context."></textarea></label>
      <button class="v3-primary-btn" type="submit" name="v3_create_support_case" value="1">Open support case</button>
    </form>
  </aside>
</div>
<?php v3PageEnd(); ?>