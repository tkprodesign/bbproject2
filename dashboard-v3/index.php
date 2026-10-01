<?php
require_once __DIR__ . '/_app.php';
require_once __DIR__ . '/_layout.php';

$profile = v3Profile($user_email, $user_name);
$client = v3ClientMeta($user_email);
$unreadNotifications = v3UnreadNotificationCount($user_email);
$accounts = v3Accounts($user_email);

$db = connectToDatabase();
$stmt = $db->prepare("SELECT type, description, amount, currency, status, time
    FROM transactions WHERE user_email = ? ORDER BY time DESC LIMIT 7");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($txType,$txDescription,$txAmount,$txCurrency,$txStatus,$txTime);
$transactions = [];
while ($stmt->fetch()) {
    $statusKey = strtolower(trim((string)$txStatus));
    if ($statusKey === 'completed') $statusKey = 'successful';
    if ($statusKey === '' || $statusKey === 'current') $statusKey = 'posted';
    $transactions[] = [
        'type' => ucwords(str_replace(['_','-'],' ',(string)$txType)),
        'description' => (string)$txDescription,
        'amount' => (float)$txAmount,
        'currency' => strtoupper((string)$txCurrency),
        'status' => ucwords(str_replace(['_','-'],' ',$statusKey)),
        'status_key' => $statusKey,
        'date' => date('M d, Y',(int)$txTime),
    ];
}
$stmt->close();

$stmt = $db->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND LOWER(status) = 'pending'");
$stmt->bind_param('s',$user_email);
$stmt->execute();
$stmt->bind_result($pendingCount);
$stmt->fetch();
$stmt->close();

$stmt = $db->prepare("SELECT COUNT(*) FROM transactions WHERE user_email = ? AND LOWER(status) IN ('successful','completed')");
$stmt->bind_param('s',$user_email);
$stmt->execute();
$stmt->bind_result($successfulCount);
$stmt->fetch();
$stmt->close();

$stmt = $db->prepare("SELECT COUNT(*) FROM fx_trades WHERE user_email = ? AND LOWER(status) = 'executed'");
$stmt->bind_param('s',$user_email);
$stmt->execute();
$stmt->bind_result($tradeCount);
$stmt->fetch();
$stmt->close();
$db->close();

$primary = $accounts[0] ?? null;
v3PageStart('Overview','overview',$profile,$user_profile_picture);
v3FlashMessages();
?>
<section class="v3-heading">
    <div>
        <span class="v3-kicker">ACCOUNT OVERVIEW</span>
        <h1>Good day, <?php echo htmlspecialchars(explode(' ', $profile['name'])[0]); ?></h1>
        <p>Your balances stay in the currency of each account. Currency changes are completed through a bank exchange trade.</p>
    </div>
    <a class="v3-primary-btn" href="/dashboard-v3/transfer/"><span class="material-symbols-rounded">north_east</span>Send money</a>
</section>

<section class="v3-overview-grid">
    <article class="v3-balance-hero">
        <div class="v3-balance-top">
            <div><span>PRIMARY ACCOUNT</span><strong><?php echo htmlspecialchars($primary['account_type'] ?? 'No active account'); ?></strong></div>
            <?php if ($primary): ?><span class="v3-badge light"><?php echo htmlspecialchars($primary['currency']); ?></span><?php endif; ?>
        </div>
        <?php if ($primary): ?>
            <div class="v3-hero-amount"><?php echo htmlspecialchars(velmoraFormatCurrency((float)$primary['balance'], $primary['currency'])); ?></div>
            <div class="v3-hero-foot">
                <div><span>Account</span><strong>•••• <?php echo htmlspecialchars(substr((string)$primary['account_number'],-4)); ?></strong></div>
                <div><span>Status</span><strong><?php echo htmlspecialchars($primary['account_status']); ?></strong></div>
            </div>
        <?php else: ?>
            <div class="v3-empty-light">No active account is attached to this profile.</div>
        <?php endif; ?>
    </article>

    <article class="v3-panel v3-bank-action">
        <div class="v3-icon-box"><span class="material-symbols-rounded">currency_exchange</span></div>
        <span class="v3-kicker">FOREIGN EXCHANGE</span>
        <h2>Exchange through the bank</h2>
        <p>Move funds between your currency accounts using a quoted bank rate. Review the trade before it is executed.</p>
        <a href="/dashboard-v3/exchange/">Start an exchange <span class="material-symbols-rounded">arrow_forward</span></a>
    </article>
</section>

<section class="v3-stat-row">
    <div><span class="material-symbols-rounded">account_balance_wallet</span><p>Active accounts<strong><?php echo count($accounts); ?></strong></p></div>
    <div><span class="material-symbols-rounded">check_circle</span><p>Successful transactions<strong><?php echo (int)$successfulCount; ?></strong></p></div>
    <div><span class="material-symbols-rounded">schedule</span><p>Pending transactions<strong><?php echo (int)$pendingCount; ?></strong></p></div>
    <div><span class="material-symbols-rounded">currency_exchange</span><p>Executed FX trades<strong><?php echo (int)$tradeCount; ?></strong></p></div>
</section>

<section class="v3-account-strip">
    <div class="v3-section-head">
        <div><span class="v3-kicker">YOUR ACCOUNTS</span><h2>Balances by currency</h2></div>
        <a href="/dashboard-v3/accounts/">Manage accounts</a>
    </div>
    <div class="v3-account-cards">
        <?php foreach ($accounts as $account): ?>
            <article>
                <div class="v3-account-card-head"><span><?php echo htmlspecialchars($account['account_type']); ?></span><b><?php echo htmlspecialchars($account['currency']); ?></b></div>
                <strong><?php echo htmlspecialchars(velmoraFormatCurrency((float)$account['balance'],$account['currency'])); ?></strong>
                <small>•••• <?php echo htmlspecialchars(substr((string)$account['account_number'],-4)); ?> · <?php echo htmlspecialchars($account['account_status']); ?></small>
            </article>
        <?php endforeach; ?>
        <?php if (empty($accounts)): ?><p class="v3-muted">No accounts yet.</p><?php endif; ?>
    </div>
</section>

<div class="v3-two-col">
    <section class="v3-panel">
        <div class="v3-section-head">
            <div><span class="v3-kicker">RECENT ACTIVITY</span><h2>Transactions</h2></div>
            <a href="/dashboard-v3/transactions/">View all</a>
        </div>
        <div class="v3-tx-list">
            <?php foreach ($transactions as $tx): $credit=$tx['amount']>=0; ?>
                <div class="v3-tx">
                    <span class="v3-tx-icon <?php echo $credit?'credit':'debit'; ?>"><span class="material-symbols-rounded"><?php echo $credit?'south_west':'north_east'; ?></span></span>
                    <div class="v3-tx-copy"><strong><?php echo htmlspecialchars($tx['description']); ?></strong><small><?php echo htmlspecialchars($tx['date'].' · '.$tx['type']); ?></small></div>
                    <span class="v3-status <?php echo htmlspecialchars($tx['status_key']); ?>"><?php echo htmlspecialchars($tx['status']); ?></span>
                    <strong class="v3-tx-money <?php echo $credit?'credit':''; ?>"><?php echo $credit?'+':'-'; ?><?php echo htmlspecialchars(velmoraFormatCurrency(abs($tx['amount']),$tx['currency'])); ?></strong>
                </div>
            <?php endforeach; ?>
            <?php if (empty($transactions)): ?><div class="v3-empty">No transactions yet.</div><?php endif; ?>
        </div>
    </section>

    <aside class="v3-panel">
        <div class="v3-section-head"><div><span class="v3-kicker">PROFILE</span><h2>Account holder</h2></div></div>
        <dl class="v3-detail-list">
            <div><dt>Name</dt><dd><?php echo htmlspecialchars($profile['name']); ?></dd></div>
            <div><dt>Occupation</dt><dd><?php echo htmlspecialchars($profile['occupation']); ?></dd></div>
            <div><dt>KYC status</dt><dd><span class="v3-verified"><?php echo htmlspecialchars($profile['status']); ?></span></dd></div>
            <div><dt>Country</dt><dd><?php echo htmlspecialchars($profile['country']); ?></dd></div>
            <div><dt>Customer number</dt><dd><?php echo htmlspecialchars($client['customer_number']); ?></dd></div>
            <div><dt>Relationship status</dt><dd><span class="v3-verified"><?php echo htmlspecialchars($client['user_status']); ?></span></dd></div>
            <div><dt>Member since</dt><dd><?php echo htmlspecialchars($client['member_since']); ?></dd></div>
            <div><dt>Notifications</dt><dd><?php echo (int)$unreadNotifications; ?> unread</dd></div>
        </dl>
        <a class="v3-text-link" href="/dashboard-v3/profile/">View full profile <span class="material-symbols-rounded">arrow_forward</span></a>
    </aside>
</div>
<?php v3PageEnd(); ?>