<?php
require_once __DIR__ . '/../dashboard/app.php';

$displayCurrency = strtoupper(trim((string)($_GET['display_currency'] ?? ($_SESSION['display_currency'] ?? ''))));
if (!velmoraIsSupportedCurrency($displayCurrency)) {
    $displayCurrency = 'USD';
}
$_SESSION['display_currency'] = $displayCurrency;

$db = connectToDatabase();

$profile = [
    'holder' => $user_name,
    'dob' => 'Not available',
    'occupation' => 'Not available',
    'kyc_status' => 'Not Submitted',
];

$stmt = $db->prepare("SELECT first_name, middle_name, last_name, date_of_birth, occupation, status
                      FROM kyc_data WHERE email = ? ORDER BY id DESC LIMIT 1");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($firstName, $middleName, $lastName, $dob, $occupation, $kycStatus);
if ($stmt->fetch()) {
    $fullName = trim(implode(' ', array_filter([$firstName, $middleName, $lastName])));
    if ($fullName !== '') $profile['holder'] = $fullName;
    if (!empty($dob)) $profile['dob'] = $dob;
    if (!empty($occupation)) $profile['occupation'] = $occupation;
    if (!empty($kycStatus)) $profile['kyc_status'] = $kycStatus;
}
$stmt->close();

$accounts = [];
$stmt = $db->prepare("SELECT a.account_number, a.account_type, a.currency, a.account_status,
                             COALESCE(SUM(CASE WHEN t.status IS NULL OR LOWER(t.status) <> 'failed' THEN t.amount ELSE 0 END), 0) AS balance
                      FROM accounts a
                      LEFT JOIN transactions t ON t.account_number = a.account_number
                      WHERE a.user_email = ?
                      GROUP BY a.account_number, a.account_type, a.currency, a.account_status
                      ORDER BY a.id DESC");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$result = $stmt->get_result();

$totalBalance = 0.0;
while ($row = $result->fetch_assoc()) {
    $currency = strtoupper((string)$row['currency']);
    if (!velmoraIsSupportedCurrency($currency)) $currency = 'USD';

    $balance = (float)$row['balance'];
    try {
        $totalBalance += velmoraConvertForValuation($balance, $currency, $displayCurrency);
    } catch (Throwable $e) {}

    $accounts[] = [
        'number' => (string)$row['account_number'],
        'type' => (string)$row['account_type'],
        'currency' => $currency,
        'status' => (string)$row['account_status'],
        'balance' => $balance,
    ];
}
$stmt->close();

$totalCredits = 0.0;
$totalDebits = 0.0;
$pendingCount = 0;
$successfulCount = 0;

$stmt = $db->prepare("SELECT amount, currency, status FROM transactions WHERE user_email = ?");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($sumAmount, $sumCurrency, $sumStatus);
while ($stmt->fetch()) {
    $currency = strtoupper((string)$sumCurrency);
    if (!velmoraIsSupportedCurrency($currency)) $currency = 'USD';

    $statusKey = strtolower(trim((string)$sumStatus));
    if ($statusKey === 'completed') $statusKey = 'successful';
    if ($statusKey === 'pending') $pendingCount++;
    if ($statusKey === 'successful') $successfulCount++;
    if ($statusKey === 'failed') continue;

    try {
        $converted = velmoraConvertForValuation(abs((float)$sumAmount), $currency, $displayCurrency);
    } catch (Throwable $e) {
        continue;
    }

    if ((float)$sumAmount >= 0) $totalCredits += $converted;
    else $totalDebits += $converted;
}
$stmt->close();

$transactions = [];
$stmt = $db->prepare("SELECT type, description, amount, currency, status, time
                      FROM transactions
                      WHERE user_email = ?
                      ORDER BY time DESC
                      LIMIT 8");
$stmt->bind_param('s', $user_email);
$stmt->execute();
$stmt->bind_result($txType, $txDescription, $txAmount, $txCurrency, $txStatus, $txTime);
while ($stmt->fetch()) {
    $currency = strtoupper((string)$txCurrency);
    if (!velmoraIsSupportedCurrency($currency)) $currency = 'USD';

    $statusKey = strtolower(trim((string)$txStatus));
    if ($statusKey === 'completed') $statusKey = 'successful';
    if ($statusKey === '' || $statusKey === 'current') $statusKey = 'posted';

    $transactions[] = [
        'type' => ucwords(str_replace(['_', '-'], ' ', (string)$txType)),
        'description' => (string)$txDescription,
        'amount' => (float)$txAmount,
        'currency' => $currency,
        'status' => ucwords(str_replace(['_', '-'], ' ', $statusKey)),
        'status_key' => $statusKey,
        'date' => date('M d, Y', (int)$txTime),
        'time' => date('H:i', (int)$txTime),
    ];
}
$stmt->close();
$db->close();

$primaryAccount = $accounts[0] ?? null;
$firstNameDisplay = explode(' ', trim((string)$profile['holder']))[0] ?? 'there';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Private Banking Dashboard | Velmora Bank</title>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="stylesheet" href="/assets/stylesheets/dashboard-v2.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
</head>
<body>
<div class="v2-demo-pill">DEMO ENVIRONMENT</div>

<div class="v2-shell">
    <aside class="v2-sidebar" id="v2Sidebar">
        <div class="v2-brand">
            <a href="/" aria-label="Velmora Bank home">
                <img src="/assets/images/branding/velmora/logo.png" alt="Velmora Bank">
            </a>
        </div>

        <nav class="v2-nav" aria-label="Dashboard navigation">
            <p class="v2-nav-label">OVERVIEW</p>
            <a class="active" href="/dashboard-v2/"><span class="material-symbols-rounded">grid_view</span><span>Overview</span></a>
            <a href="/dashboard/accounts/transactions"><span class="material-symbols-rounded">receipt_long</span><span>Transactions</span></a>

            <p class="v2-nav-label">BANKING</p>
            <a href="/dashboard/fund/transfer"><span class="material-symbols-rounded">send_money</span><span>Transfer Funds</span></a>
            <a href="/dashboard/accounts"><span class="material-symbols-rounded">account_balance_wallet</span><span>Accounts</span></a>
            <a href="/dashboard/accounts/create"><span class="material-symbols-rounded">add_card</span><span>Open Account</span></a>

            <p class="v2-nav-label">PROFILE</p>
            <a href="/dashboard/profile"><span class="material-symbols-rounded">person</span><span>Profile</span></a>
            <a href="/dashboard/security/preferences"><span class="material-symbols-rounded">shield_lock</span><span>Security</span></a>
        </nav>

        <div class="v2-sidebar-foot">
            <a href="/contact/"><span class="material-symbols-rounded">support_agent</span><span>Support</span></a>
            <a href="?logout=1"><span class="material-symbols-rounded">logout</span><span>Sign out</span></a>
        </div>
    </aside>

    <div class="v2-sidebar-overlay" id="v2SidebarOverlay"></div>

    <main class="v2-main">
        <header class="v2-topbar">
            <div class="v2-topbar-left">
                <button class="v2-menu-button" id="v2MenuButton" type="button" aria-label="Open menu">
                    <span class="material-symbols-rounded">menu</span>
                </button>
                <div>
                    <p class="v2-eyebrow">PRIVATE BANKING</p>
                    <h1>Good day, <?php echo htmlspecialchars($firstNameDisplay); ?></h1>
                </div>
            </div>

            <div class="v2-user">
                <div class="v2-user-copy">
                    <strong><?php echo htmlspecialchars($profile['holder']); ?></strong>
                    <span><?php echo htmlspecialchars($profile['occupation']); ?></span>
                </div>
                <?php if ($user_profile_picture && $user_profile_picture !== 'nil'): ?>
                    <img src="/dashboard/security/complete-kyc/uploads/<?php echo htmlspecialchars($user_profile_picture); ?>" alt="Profile picture">
                <?php else: ?>
                    <div class="v2-avatar-fallback"><?php echo htmlspecialchars(strtoupper(substr($firstNameDisplay, 0, 1))); ?></div>
                <?php endif; ?>
            </div>
        </header>

        <section class="v2-content">
            <div class="v2-hero-grid">
                <article class="v2-balance-card">
                    <div class="v2-balance-head">
                        <div>
                            <span>Total portfolio balance</span>
                            <small>Across <?php echo count($accounts); ?> active account<?php echo count($accounts) === 1 ? '' : 's'; ?></small>
                        </div>
                        <form method="get" class="v2-currency-form">
                            <select name="display_currency" onchange="this.form.submit()" aria-label="Display currency">
                                <?php echo velmoraCurrencyOptions($displayCurrency); ?>
                            </select>
                        </form>
                    </div>

                    <div class="v2-balance-value"><?php echo htmlspecialchars(velmoraFormatCurrency($totalBalance, $displayCurrency)); ?></div>

                    <div class="v2-balance-meta">
                        <div><span>Credits</span><strong><?php echo htmlspecialchars(velmoraFormatCurrency($totalCredits, $displayCurrency)); ?></strong></div>
                        <div><span>Debits</span><strong><?php echo htmlspecialchars(velmoraFormatCurrency($totalDebits, $displayCurrency)); ?></strong></div>
                    </div>
                </article>

                <article class="v2-primary-account">
                    <div class="v2-card-title-row">
                        <div>
                            <span class="v2-kicker">PRIMARY ACCOUNT</span>
                            <h2><?php echo htmlspecialchars($primaryAccount['type'] ?? 'No account'); ?></h2>
                        </div>
                        <span class="v2-status-dot"><?php echo htmlspecialchars($primaryAccount['status'] ?? 'Unavailable'); ?></span>
                    </div>

                    <?php if ($primaryAccount): ?>
                        <div class="v2-account-number">•••• •••• <?php echo htmlspecialchars(substr($primaryAccount['number'], -4)); ?></div>
                        <div class="v2-account-foot">
                            <div><span>Currency</span><strong><?php echo htmlspecialchars($primaryAccount['currency']); ?></strong></div>
                            <div><span>Balance</span><strong><?php echo htmlspecialchars(velmoraFormatCurrency($primaryAccount['balance'], $primaryAccount['currency'])); ?></strong></div>
                        </div>
                    <?php else: ?>
                        <p class="v2-muted">No active account is currently attached to this profile.</p>
                    <?php endif; ?>
                </article>
            </div>

            <section class="v2-quick-actions">
                <a href="/dashboard/fund/transfer"><span class="material-symbols-rounded">north_east</span><div><strong>Send money</strong><small>Make a bank transfer</small></div></a>
                <a href="/dashboard/accounts"><span class="material-symbols-rounded">account_balance</span><div><strong>Manage accounts</strong><small>View account details</small></div></a>
                <a href="/dashboard/accounts/transactions"><span class="material-symbols-rounded">history</span><div><strong>Transaction history</strong><small>Review all activity</small></div></a>
                <a href="/dashboard/security/preferences"><span class="material-symbols-rounded">verified_user</span><div><strong>Security center</strong><small>Manage security settings</small></div></a>
            </section>

            <div class="v2-data-grid">
                <section class="v2-panel v2-transactions-panel">
                    <div class="v2-panel-head">
                        <div>
                            <span class="v2-kicker">ACTIVITY</span>
                            <h2>Recent transactions</h2>
                        </div>
                        <a href="/dashboard/accounts/transactions">View all <span class="material-symbols-rounded">arrow_forward</span></a>
                    </div>

                    <div class="v2-transaction-list">
                        <?php if (empty($transactions)): ?>
                            <div class="v2-empty">No transactions yet.</div>
                        <?php else: ?>
                            <?php foreach ($transactions as $tx):
                                $isCredit = $tx['amount'] >= 0;
                                $icon = $isCredit ? 'south_west' : 'north_east';
                            ?>
                                <div class="v2-transaction-row">
                                    <div class="v2-tx-icon <?php echo $isCredit ? 'credit' : 'debit'; ?>">
                                        <span class="material-symbols-rounded"><?php echo $icon; ?></span>
                                    </div>
                                    <div class="v2-tx-main">
                                        <strong><?php echo htmlspecialchars($tx['description']); ?></strong>
                                        <span><?php echo htmlspecialchars($tx['date']); ?> · <?php echo htmlspecialchars($tx['type']); ?></span>
                                    </div>
                                    <div class="v2-tx-status">
                                        <span class="status-<?php echo htmlspecialchars($tx['status_key']); ?>"><?php echo htmlspecialchars($tx['status']); ?></span>
                                    </div>
                                    <div class="v2-tx-amount <?php echo $isCredit ? 'credit' : 'debit'; ?>">
                                        <?php echo $isCredit ? '+' : '-'; ?><?php echo htmlspecialchars(velmoraFormatCurrency(abs($tx['amount']), $tx['currency'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </section>

                <aside class="v2-side-stack">
                    <section class="v2-panel">
                        <div class="v2-panel-head compact">
                            <div>
                                <span class="v2-kicker">PROFILE</span>
                                <h2>Account details</h2>
                            </div>
                        </div>
                        <dl class="v2-profile-list">
                            <div><dt>Account holder</dt><dd><?php echo htmlspecialchars($profile['holder']); ?></dd></div>
                            <div><dt>Occupation</dt><dd><?php echo htmlspecialchars($profile['occupation']); ?></dd></div>
                            <div><dt>Date of birth</dt><dd><?php echo htmlspecialchars($profile['dob']); ?></dd></div>
                            <div><dt>KYC status</dt><dd><span class="v2-verified"><?php echo htmlspecialchars($profile['kyc_status']); ?></span></dd></div>
                        </dl>
                    </section>

                    <section class="v2-panel v2-health-panel">
                        <div class="v2-panel-head compact">
                            <div>
                                <span class="v2-kicker">ACCOUNT HEALTH</span>
                                <h2>Status overview</h2>
                            </div>
                        </div>
                        <div class="v2-health-grid">
                            <div><span>Successful</span><strong><?php echo (int)$successfulCount; ?></strong></div>
                            <div><span>Pending</span><strong><?php echo (int)$pendingCount; ?></strong></div>
                            <div><span>Accounts</span><strong><?php echo count($accounts); ?></strong></div>
                        </div>
                    </section>
                </aside>
            </div>
        </section>
    </main>
</div>

<script src="/assets/scripts/dashboard-v2.js?v=<?php echo time(); ?>"></script>
</body>
</html>
