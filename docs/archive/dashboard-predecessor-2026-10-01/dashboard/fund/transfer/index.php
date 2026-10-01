<?php include('../../app.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html')?>
<?php if(isset($user_kyc_level) && $user_kyc_level == 0) :?>
    <section class="kyc-ver">
        <div class="content">
            <img src="/assets/images/kyc-ver-placeholder.png">
            <h1>PENDING KYC VERIFICATION</h1>
            <p>Your KYC Verification hasn't been processed. You can only send money when your KYC verification has passed.</p>
        </div>
    </section>
<?php else: ?>
    <section class="add-account" name="user withdraw">
        <div class="container">
            <?php
            $transferMessage = $_GET['transfer'] ?? '';
            if ($transferMessage === 'insufficient') {
                echo '<div class="fx-quote-card" style="margin-bottom:14px;border-color:#efc7c7;background:#fff6f6;"><strong>Insufficient funds in the selected account.</strong></div>';
            } elseif (in_array($transferMessage, ['invalid','account','fx','failed'], true)) {
                echo '<div class="fx-quote-card" style="margin-bottom:14px;border-color:#efc7c7;background:#fff6f6;"><strong>We could not prepare that transfer. Check the details and try again.</strong></div>';
            }

            $db = connectToDatabase();
            $stmt = $db->prepare("SELECT a.account_number, a.account_type, a.currency, COALESCE(SUM(CASE WHEN t.status IS NULL OR LOWER(t.status) <> 'failed' THEN t.amount ELSE 0 END), 0) AS balance
                FROM accounts a
                LEFT JOIN transactions t ON t.account_number = a.account_number
                WHERE a.user_email = ? AND a.account_status = 'Active'
                GROUP BY a.account_number, a.account_type, a.currency
                ORDER BY a.id DESC");
            $stmt->bind_param('s', $user_email);
            $stmt->execute();
            $accountsResult = $stmt->get_result();
            $transferAccounts = [];
            while ($row = $accountsResult->fetch_assoc()) {
                $transferAccounts[] = $row;
            }
            $stmt->close();
            $db->close();
            $fxClientConfig = velmoraFxClientConfig();
            ?>
            <form action="" method="post" data-fx-form data-fx-mode="transfer">
                <h2>Transfer</h2>
                <div class="input-box">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name" class="dark-bg" required>
                </div>
                <div class="input-box">
                    <label>Account Number</label>
                    <input type="text" inputmode="numeric" name="account_number" class="dark-bg" required>
                </div>
                <div class="input-box">
                    <label>Account Type</label>
                    <select name="account_type">
                        <option value="Savings">Savings</option>
                        <option value="Current">Current</option>
                        <option value="Not Sure">Not Sure/Others</option>
                    </select>
                </div>
                <div class="input-box">
                    <label>From Account</label>
                    <?php if (!empty($transferAccounts)): ?>
                        <select name="from_account" data-fx-source-account required>
                            <?php foreach ($transferAccounts as $account): ?>
                                <option value="<?php echo htmlspecialchars($account['account_number']); ?>"
                                        data-currency="<?php echo htmlspecialchars($account['currency']); ?>">
                                    <?php echo htmlspecialchars($account['account_number'] . ' — ' . $account['account_type'] . ' — ' . $account['currency'] . ' ' . number_format((float)$account['balance'], 2)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <select name="from_account" disabled><option>No active accounts</option></select>
                    <?php endif; ?>
                </div>
                <div class="input-box">
                    <label>Amount to Send</label>
                    <input type="number" name="amount" min="0.01" step="0.01" inputmode="decimal" data-fx-amount required>
                </div>
                <div class="input-box">
                    <label>Recipient Currency</label>
                    <select name="currency" data-fx-currency required>
                        <?php echo velmoraCurrencyOptions('USD'); ?>
                    </select>
                </div>

                <div class="fx-quote-card is-waiting" data-fx-quote>
                    <div class="fx-title">Currency conversion preview</div>
                    <div class="fx-quote-grid">
                        <div><span>You send</span><strong data-fx-send>—</strong></div>
                        <div><span>Recipient gets</span><strong data-fx-receive>—</strong></div>
                        <div><span>Bank rate</span><strong data-fx-rate>—</strong></div>
                        <div><span>FX margin</span><strong data-fx-spread>—</strong></div>
                    </div>
                    <p class="fx-note">The displayed bank rate includes Velmora's FX spread; the market reference is used only to calculate the quote. <a href="https://www.exchangerate-api.com" target="_blank" rel="noopener nofollow">Rates by Exchange Rate API</a>.</p>
                </div>

                <div class="input-box">
                    <button type="submit" name="transfer_funds" value="1" <?php echo empty($transferAccounts) ? 'disabled' : ''; ?>>Transfer</button>
                </div>
            </form>
        </div>
    </section>
<?php endif; ?>
<script>
window.VelmoraFxConfig = <?php echo json_encode($fxClientConfig, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
</script>
<script src="/assets/scripts/fx.js?v=<?php echo time(); ?>"></script>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>