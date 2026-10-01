<?php
require_once __DIR__ . '/../../_app.php';
require_once __DIR__ . '/../../_layout.php';
$profile=v3Profile($user_email,$user_name);
$accountNumber=preg_replace('/\D+/','',(string)($_GET['account']??''));
$db=connectToDatabase();
$account=null;
if($accountNumber!==''){
 $stmt=$db->prepare("SELECT account_number,account_type,currency,account_status FROM accounts WHERE user_email=? AND account_number=? LIMIT 1");
 $stmt->bind_param('ss',$user_email,$accountNumber);$stmt->execute();$account=$stmt->get_result()->fetch_assoc()?:null;$stmt->close();
}
$db->close();
if(!$account){v3Redirect('/dashboard/accounts/');}
v3PageStart('Account Opened','accounts',$profile,$user_profile_picture);
?>
<section class="v3-success-shell">
 <div class="v3-panel">
  <div class="v3-success-mark"><span class="material-symbols-rounded">check</span></div>
  <span class="v3-kicker">ACCOUNT OPENED</span>
  <h1>Your new account is active.</h1>
  <p>The account has been added to your banking relationship. Its currency remains fixed to the denomination selected at opening.</p>
  <div class="v3-success-account">
   <div><span>Account type</span><strong><?php echo htmlspecialchars($account['account_type']); ?></strong></div>
   <div><span>Currency</span><strong><?php echo htmlspecialchars($account['currency']); ?></strong></div>
   <div><span>Account</span><strong>•••• <?php echo htmlspecialchars(substr((string)$account['account_number'],-4)); ?></strong></div>
  </div>
  <div class="v3-settings-list">
   <a href="/dashboard/accounts/"><span class="material-symbols-rounded">account_balance_wallet</span><div><strong>View all accounts</strong><small>Return to your account portfolio.</small></div><span class="material-symbols-rounded">chevron_right</span></a>
   <a href="/dashboard/exchange/"><span class="material-symbols-rounded">currency_exchange</span><div><strong>Currency exchange</strong><small>Move funds between supported currency accounts using a bank quote.</small></div><span class="material-symbols-rounded">chevron_right</span></a>
   <a href="/dashboard/identity/"><span class="material-symbols-rounded">badge</span><div><strong>Identity & KYC</strong><small>Review or complete the information attached to your profile.</small></div><span class="material-symbols-rounded">chevron_right</span></a>
  </div>
 </div>
</section>
<?php v3PageEnd(); ?>