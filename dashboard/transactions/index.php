<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';
$profile=v3Profile($user_email,$user_name);
$db=connectToDatabase();
$stmt=$db->prepare("SELECT transaction_id,type,description,amount,currency,status,time,account_number,counter_currency,counter_amount,fx_rate,channel,value_date,posted_at
    FROM transactions WHERE user_email=? AND (status IS NULL OR LOWER(status) <> 'archived') ORDER BY time DESC");
$stmt->bind_param('s',$user_email);
$stmt->execute();
$result=$stmt->get_result();
$rows=[];
while($r=$result->fetch_assoc()){$rows[]=$r;}
$stmt->close();$db->close();
v3PageStart('Transactions','transactions',$profile,$user_profile_picture);
?>
<section class="v3-heading">
    <div><span class="v3-kicker">ACTIVITY</span><h1>Transaction history</h1><p>Review deposits, transfers, withdrawals and currency trades across your accounts.</p></div>
</section>
<section class="v3-panel">
    <div class="v3-toolbar">
        <div class="v3-search"><span class="material-symbols-rounded">search</span><input id="v3TxSearch" type="search" placeholder="Search transactions"></div>
        <div class="v3-filter-group"><button class="active" data-v3-filter="all">All</button><button data-v3-filter="credit">Credits</button><button data-v3-filter="debit">Debits</button><button data-v3-filter="pending">Pending</button></div>
    </div>
    <div class="v3-table-wrap">
        <table class="v3-table" id="v3TxTable">
            <thead><tr><th>Value date</th><th>Description</th><th>Reference</th><th>Channel</th><th>Account</th><th>Status</th><th>Amount</th></tr></thead>
            <tbody>
            <?php foreach($rows as $r):
                $amount=(float)$r['amount'];$credit=$amount>=0;$status=strtolower(trim((string)$r['status']));if($status==='completed')$status='successful';
            ?>
                <tr data-kind="<?php echo $credit?'credit':'debit'; ?>" data-status="<?php echo htmlspecialchars($status); ?>">
                    <td><?php echo htmlspecialchars(!empty($r['value_date'])?date('M d, Y',strtotime((string)$r['value_date'])):date('M d, Y',(int)$r['time'])); ?><small><?php echo htmlspecialchars(!empty($r['posted_at'])?date('H:i',strtotime((string)$r['posted_at'])):date('H:i',(int)$r['time'])); ?></small></td>
                    <td><a href="/dashboard/transactions/detail/?ref=<?php echo urlencode((string)$r['transaction_id']); ?>"><strong><?php echo htmlspecialchars((string)$r['description']); ?></strong></a><small><?php echo htmlspecialchars((string)$r['type']); ?></small></td>
                    <td><a href="/dashboard/transactions/detail/?ref=<?php echo urlencode((string)$r['transaction_id']); ?>"><?php echo htmlspecialchars((string)$r['transaction_id']); ?></a></td>
                    <td><?php echo htmlspecialchars(!empty($r['channel'])?(string)$r['channel']:'Online Banking'); ?></td>
                    <td>•••• <?php echo htmlspecialchars(substr((string)$r['account_number'],-4)); ?></td>
                    <td><span class="v3-status <?php echo htmlspecialchars($status); ?>"><?php echo htmlspecialchars(ucfirst($status)); ?></span></td>
                    <td class="v3-table-amount <?php echo $credit?'credit':''; ?>"><?php echo $credit?'+':'-'; ?><?php echo htmlspecialchars(velmoraFormatCurrency(abs($amount),strtoupper((string)$r['currency']))); ?>
                        <?php if(!empty($r['counter_currency']) && $r['counter_amount']!==null): ?><small>Countervalue <?php echo htmlspecialchars(velmoraFormatCurrency((float)$r['counter_amount'],strtoupper((string)$r['counter_currency']))); ?></small><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="v3-empty" id="v3TxEmpty" style="display:none">No transactions match this view.</div>
</section>
<?php v3PageEnd(); ?>