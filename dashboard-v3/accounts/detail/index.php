<?php
require_once __DIR__ . '/../../_app.php';
require_once __DIR__ . '/../../_layout.php';

$profile=v3Profile($user_email,$user_name);
$accountNumber=preg_replace('/\D+/','',(string)($_GET['account']??''));
$db=connectToDatabase();
$account=null;
$stmt=$db->prepare("SELECT account_number,account_type,currency,account_status,creation_time,account_alias,opened_at FROM accounts WHERE user_email=? AND account_number=? LIMIT 1");
$stmt->bind_param('ss',$user_email,$accountNumber);$stmt->execute();$account=$stmt->get_result()->fetch_assoc()?:null;$stmt->close();
if(!$account){$db->close();v3Redirect('/dashboard-v3/accounts/');}
$balance=v3AccountBalance($db,$accountNumber);
$stmt=$db->prepare("SELECT transaction_id,type,description,amount,currency,status,time,value_date,channel FROM transactions WHERE user_email=? AND account_number=? ORDER BY time DESC LIMIT 10");
$stmt->bind_param('ss',$user_email,$accountNumber);$stmt->execute();$txs=$stmt->get_result()->fetch_all(MYSQLI_ASSOC);$stmt->close();$db->close();

v3PageStart('Account Detail','accounts',$profile,$user_profile_picture);
?>
<section class="v3-heading">
 <div><span class="v3-kicker">ACCOUNT DETAIL</span><h1><?php echo htmlspecialchars($account['account_alias']?:$account['account_type']); ?></h1><p>Review the account’s fixed currency, status, available ledger balance and recent activity.</p></div>
 <a class="v3-secondary-btn" href="/dashboard-v3/accounts/">Back to accounts</a>
</section>
<section class="v3-panel" style="margin-bottom:18px">
 <div class="v3-meta-grid">
  <div><span>Available balance</span><strong><?php echo htmlspecialchars(velmoraFormatCurrency($balance,$account['currency'])); ?></strong></div>
  <div><span>Currency</span><strong><?php echo htmlspecialchars($account['currency']); ?></strong></div>
  <div><span>Status</span><strong class="v3-verified"><?php echo htmlspecialchars($account['account_status']); ?></strong></div>
  <div><span>Account number</span><strong><?php echo htmlspecialchars($account['account_number']); ?></strong></div>
  <div><span>Account type</span><strong><?php echo htmlspecialchars($account['account_type']); ?></strong></div>
  <div><span>Opened</span><strong><?php echo !empty($account['opened_at'])?htmlspecialchars(date('M d, Y',strtotime($account['opened_at']))):htmlspecialchars(date('M d, Y',(int)$account['creation_time'])); ?></strong></div>
 </div>
 <div class="v3-receipt-actions"><a class="v3-primary-btn" href="/dashboard-v3/transfer/?from=<?php echo urlencode($accountNumber); ?>">Transfer funds</a><a class="v3-secondary-btn" href="/dashboard-v3/exchange/?from=<?php echo urlencode($accountNumber); ?>">Exchange currency</a><a class="v3-secondary-btn" href="/dashboard-v3/statements/?account=<?php echo urlencode($accountNumber); ?>">Statements</a></div>
</section>
<section class="v3-panel">
 <div class="v3-section-head"><div><span class="v3-kicker">ACTIVITY</span><h2>Recent transactions</h2></div><a href="/dashboard-v3/transactions/">All transactions</a></div>
 <div class="v3-list">
 <?php foreach($txs as $tx): $credit=(float)$tx['amount']>=0; ?>
  <a class="v3-list-item" href="/dashboard-v3/transactions/detail/?ref=<?php echo urlencode($tx['transaction_id']); ?>">
   <span class="v3-list-icon"><span class="material-symbols-rounded"><?php echo $credit?'south_west':'north_east'; ?></span></span>
   <div><strong><?php echo htmlspecialchars($tx['description']); ?></strong><small><?php echo htmlspecialchars(($tx['value_date']?:date('Y-m-d',(int)$tx['time'])).' · '.($tx['channel']?:'Online Banking').' · '.$tx['status']); ?></small></div>
   <strong class="<?php echo $credit?'v3-verified':''; ?>"><?php echo htmlspecialchars(velmoraFormatCurrency((float)$tx['amount'],$tx['currency'])); ?></strong>
  </a>
 <?php endforeach; ?>
 <?php if(empty($txs)): ?><div class="v3-empty">No activity on this account yet.</div><?php endif; ?>
 </div>
</section>
<?php v3PageEnd(); ?>