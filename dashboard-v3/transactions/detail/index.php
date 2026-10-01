<?php
require_once __DIR__ . '/../../_app.php';
require_once __DIR__ . '/../../_layout.php';

$profile=v3Profile($user_email,$user_name);
$ref=trim((string)($_GET['ref']??''));
$db=connectToDatabase();
$stmt=$db->prepare("SELECT transaction_id,type,description,amount,currency,status,time,account_number,to_bank_name,to_account_type,to_account_number,counter_currency,counter_amount,fx_rate,fx_spread_bps,channel,value_date,posted_at,created_at FROM transactions WHERE user_email=? AND transaction_id=? LIMIT 1");
$stmt->bind_param('ss',$user_email,$ref);$stmt->execute();$tx=$stmt->get_result()->fetch_assoc()?:null;$stmt->close();$db->close();
if(!$tx)v3Redirect('/dashboard-v3/transactions/');

v3PageStart('Transaction Receipt','transactions',$profile,$user_profile_picture);
?>
<section class="v3-receipt">
 <div class="v3-panel">
  <div class="v3-receipt-head"><div><span class="v3-kicker">TRANSACTION RECEIPT</span><h1><?php echo htmlspecialchars($tx['type']); ?></h1><span class="v3-status <?php echo htmlspecialchars(strtolower((string)$tx['status'])); ?>"><?php echo htmlspecialchars($tx['status']); ?></span></div><div><span class="v3-kicker">REFERENCE</span><strong style="display:block;margin-top:6px;font-size:11px"><?php echo htmlspecialchars($tx['transaction_id']); ?></strong></div></div>
  <div class="v3-receipt-amount <?php echo (float)$tx['amount']>=0?'v3-verified':''; ?>"><?php echo htmlspecialchars(velmoraFormatCurrency((float)$tx['amount'],$tx['currency'])); ?></div>
  <dl class="v3-detail-grid">
   <div class="wide"><dt>Description</dt><dd><?php echo htmlspecialchars($tx['description']); ?></dd></div>
   <div><dt>Account</dt><dd>•••• <?php echo htmlspecialchars(substr((string)$tx['account_number'],-4)); ?></dd></div>
   <div><dt>Currency</dt><dd><?php echo htmlspecialchars($tx['currency']); ?></dd></div>
   <div><dt>Value date</dt><dd><?php echo htmlspecialchars($tx['value_date']?:date('Y-m-d',(int)$tx['time'])); ?></dd></div>
   <div><dt>Posted</dt><dd><?php echo htmlspecialchars($tx['posted_at']?:date('Y-m-d H:i:s',(int)$tx['time'])); ?></dd></div>
   <div><dt>Channel</dt><dd><?php echo htmlspecialchars($tx['channel']?:'Online Banking'); ?></dd></div>
   <div><dt>Status</dt><dd><?php echo htmlspecialchars($tx['status']); ?></dd></div>
   <?php if(!empty($tx['to_bank_name'])): ?><div><dt>Recipient bank</dt><dd><?php echo htmlspecialchars($tx['to_bank_name']); ?></dd></div><?php endif; ?>
   <?php if(!empty($tx['to_account_number'])): ?><div><dt>Recipient account</dt><dd><?php echo htmlspecialchars($tx['to_account_number']); ?></dd></div><?php endif; ?>
   <?php if(!empty($tx['to_account_type'])): ?><div><dt>Recipient account type</dt><dd><?php echo htmlspecialchars($tx['to_account_type']); ?></dd></div><?php endif; ?>
   <?php if(!empty($tx['counter_currency']) && $tx['counter_amount']!==null): ?><div><dt>Countervalue</dt><dd><?php echo htmlspecialchars(velmoraFormatCurrency((float)$tx['counter_amount'],$tx['counter_currency'])); ?></dd></div><?php endif; ?>
   <?php if($tx['fx_rate']!==null): ?><div><dt>FX rate</dt><dd><?php echo htmlspecialchars(number_format((float)$tx['fx_rate'],6)); ?></dd></div><?php endif; ?>
   <?php if($tx['fx_spread_bps']!==null): ?><div><dt>FX margin</dt><dd><?php echo htmlspecialchars(number_format(((int)$tx['fx_spread_bps'])/100,2)); ?>%</dd></div><?php endif; ?>
  </dl>
  <div class="v3-receipt-actions"><button class="v3-primary-btn" type="button" onclick="window.print()"><span class="material-symbols-rounded">print</span>Print receipt</button><a class="v3-secondary-btn" href="/dashboard-v3/transactions/">Back to transactions</a></div>
 </div>
</section>
<?php v3PageEnd(); ?>