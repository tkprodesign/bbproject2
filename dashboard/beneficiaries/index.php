<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';

$profile=v3Profile($user_email,$user_name);
$beneficiaries=v3Beneficiaries($user_email,true);

v3PageStart('Beneficiaries','beneficiaries',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
  <div><span class="v3-kicker">PAYEE DIRECTORY</span><h1>Beneficiaries</h1><p>Save trusted recipient details so future transfers can be prepared with less repetitive data entry.</p></div>
  <a class="v3-primary-btn" href="/dashboard/transfer/"><span class="material-symbols-rounded">north_east</span>New transfer</a>
</section>

<div class="v3-two-col">
  <section class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">SAVED PAYEES</span><h2><?php echo count($beneficiaries); ?> active</h2></div></div>
    <div class="v3-list">
      <?php foreach($beneficiaries as $b): ?>
      <article class="v3-list-item">
        <span class="v3-list-icon"><span class="material-symbols-rounded">person</span></span>
        <div>
          <strong><?php echo htmlspecialchars($b['beneficiary_name']); ?></strong>
          <small><?php echo htmlspecialchars($b['bank_name']); ?> · •••• <?php echo htmlspecialchars(substr((string)$b['account_number'],-4)); ?> · <?php echo htmlspecialchars($b['currency']); ?><?php if(!empty($b['nickname'])): ?> · <?php echo htmlspecialchars($b['nickname']); ?><?php endif; ?></small>
        </div>
        <div class="v3-list-actions">
          <a href="/dashboard/transfer/?beneficiary=<?php echo (int)$b['id']; ?>">Pay</a>
          <form method="post" onsubmit="return confirm('Remove this beneficiary?');">
            <?php echo v3CsrfInput(); ?>
            <input type="hidden" name="beneficiary_id" value="<?php echo (int)$b['id']; ?>">
            <button class="danger" type="submit" name="v3_remove_beneficiary" value="1">Remove</button>
          </form>
        </div>
      </article>
      <?php endforeach; ?>
      <?php if(empty($beneficiaries)): ?><div class="v3-empty">No saved beneficiaries yet.</div><?php endif; ?>
    </div>
  </section>

  <aside class="v3-panel">
    <div class="v3-section-head"><div><span class="v3-kicker">ADD PAYEE</span><h2>Save beneficiary</h2></div></div>
    <p class="v3-form-note">Save only recipient information you have verified independently.</p>
    <form method="post" class="v3-form">
      <?php echo v3CsrfInput(); ?>
      <label><span>Beneficiary name</span><input type="text" name="beneficiary_name" required></label>
      <label><span>Nickname (optional)</span><input type="text" name="nickname" maxlength="100"></label>
      <label><span>Bank name</span><input type="text" name="bank_name" required></label>
      <label><span>Account number</span><input type="text" name="account_number" required></label>
      <div class="v3-form-row">
        <label><span>Account type</span><select name="account_type"><option>Current</option><option>Savings</option><option>Not Sure</option></select></label>
        <label><span>Currency</span><select name="currency" required><?php echo velmoraCurrencyOptions('USD'); ?></select></label>
      </div>
      <button class="v3-primary-btn" type="submit" name="v3_add_beneficiary" value="1">Save beneficiary</button>
    </form>
  </aside>
</div>
<?php v3PageEnd(); ?>