<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$db=connectToDatabase();
$res=$db->query("SELECT trade_id,user_email,from_account_number,to_account_number,source_currency,target_currency,source_amount,target_amount,customer_rate,fx_spread_bps,status,quoted_at,executed_at FROM fx_trades ORDER BY id DESC");
$rows=$res?$res->fetch_all(MYSQLI_ASSOC):[];$db->close();
cpv2Start('FX Trades','fx');
?>
<section class="op-heading"><div><span class="op-kicker">FOREIGN EXCHANGE</span><h1>FX trades</h1><p>Executed and quoted exchange records with both currency legs, client rate and FX margin.</p></div></section>
<section class="op-panel"><div class="op-table-wrap"><table class="op-table"><thead><tr><th>Trade</th><th>Customer</th><th>Sold</th><th>Bought</th><th>Rate</th><th>Margin</th><th>Status</th><th>Executed</th></tr></thead><tbody>
<?php foreach($rows as $r):?><tr><td><strong><?php echo htmlspecialchars($r['trade_id']); ?></strong></td><td><?php echo htmlspecialchars($r['user_email']); ?></td><td><?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['source_amount'],$r['source_currency'])); ?><small>•••• <?php echo htmlspecialchars(substr((string)$r['from_account_number'],-4)); ?></small></td><td><?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['target_amount'],$r['target_currency'])); ?><small>•••• <?php echo htmlspecialchars(substr((string)$r['to_account_number'],-4)); ?></small></td><td><?php echo htmlspecialchars(number_format((float)$r['customer_rate'],6)); ?></td><td><?php echo htmlspecialchars(number_format(((int)$r['fx_spread_bps'])/100,2)); ?>%</td><td><span class="op-status <?php echo strtolower($r['status']); ?>"><?php echo htmlspecialchars($r['status']); ?></span></td><td><?php echo $r['executed_at']?htmlspecialchars(date('M d, Y H:i',(int)$r['executed_at'])):'—'; ?></td></tr><?php endforeach;?>
<?php if(empty($rows)):?><tr><td colspan="8">No FX trades found.</td></tr><?php endif;?>
</tbody></table></div></section>
<?php cpv2End(); ?>