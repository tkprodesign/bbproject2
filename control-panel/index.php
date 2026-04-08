<?php include('app.php') ?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <title>Control Panel</title>
    <link rel="stylesheet" href="/assets/stylesheets/control-panel.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/control-panel.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="/assets/stylesheets/mobile/control-panel.css?v=<?php echo time(); ?>" media="screen and (max-width: 720px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
</head>
<body>
<header>
    <div class="container">
        <a href="/" id="logo">
            <img src="/assets/images/branding/velmora/logo.png" alt="Velmora Bank" style="height: 40px; width: auto;">
        </a>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/control-panel">Control Panel</a></li>
                <li><a href="/site-users">All Users</a></li>
                <li><a href="/kyc-data">All KYC Details</a></li>
                <li><a href="/transactions">All Transactions</a></li>
            </ul>
        </nav>
        <a href="#" id="menuToggle">
            <span class="material-symbols-outlined open">
                menu
                </span>
            <span class="material-symbols-outlined close">
                close
                </span>
        </a>
    </div>
</header>
<section class="greetings">
    <div class="container">
        <h1 id="greeting-text">Good Evening Boss</h1>
        <p>Hope you're having a wonderful day.</p>
        <p>Your controls are easy to use and you alone bear access to this page.</p>
    </div>
</section>

<?php
$controlPanelActionMessages = [
    'credit_user' => [
        'success' => ['type' => 'success', 'text' => 'Deposit posted successfully.'],
        'invalid' => ['type' => 'error', 'text' => 'Deposit failed: invalid input values.'],
        'account_mismatch' => ['type' => 'error', 'text' => 'Deposit failed: the account number does not match the provided user email.'],
        'failed' => ['type' => 'error', 'text' => 'Deposit failed due to a server/database error.'],
    ],
    'debit_user' => [
        'success' => ['type' => 'success', 'text' => 'Withdrawal recorded successfully.'],
        'invalid' => ['type' => 'error', 'text' => 'Withdrawal failed: invalid input values.'],
        'failed' => ['type' => 'error', 'text' => 'Withdrawal failed due to a server/database error.'],
    ],
    'judge_withdrawal' => [
        'success' => ['type' => 'success', 'text' => 'Withdrawal review saved successfully.'],
        'not_found' => ['type' => 'error', 'text' => 'Withdrawal review failed: transaction not found.'],
        'failed' => ['type' => 'error', 'text' => 'Withdrawal review failed due to a server/database error.'],
    ],
    'judge_kyc' => [
        'success' => ['type' => 'success', 'text' => 'KYC decision saved successfully.'],
        'not_found' => ['type' => 'error', 'text' => 'KYC decision failed: KYC record not found.'],
        'failed' => ['type' => 'error', 'text' => 'KYC decision failed due to a server/database error.'],
    ],
];

$actionFeedback = null;
foreach ($controlPanelActionMessages as $actionKey => $actionStates) {
    if (isset($_GET[$actionKey])) {
        $statusKey = $_GET[$actionKey];
        if (isset($actionStates[$statusKey])) {
            $actionFeedback = $actionStates[$statusKey];
        }
        break;
    }
}
?>

<?php if ($actionFeedback): ?>
<section class="form">
    <div class="container">
        <div style="padding: 14px 16px; border-radius: 8px; border: 1px solid <?php echo $actionFeedback['type'] === 'success' ? '#b8ebde' : '#ffd0cc'; ?>; background: <?php echo $actionFeedback['type'] === 'success' ? '#edfdf7' : '#fff2f1'; ?>; color: <?php echo $actionFeedback['type'] === 'success' ? '#0e6b4d' : '#8a1f17'; ?>;">
            <?php echo htmlspecialchars($actionFeedback['text']); ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="form">
    <div class="container">
        <form action="" method="post">
            <h2>Support Phone Number</h2>
            <div class="input-box">
                <label>Phone Number</label>
                <input type="text" name="support_phone_number" value="<?php echo htmlspecialchars(getSupportPhoneNumber()); ?>" required>
            </div>
            <div class="input-box">
                <button type="submit" name="update_support_phone" value="1">Update Number</button>
            </div>
        </form>
    </div>
</section>

<section class="table site-users">
    <div class="container">
        <h2>Site Users Short List (Max 10 Users)</h2>
        <?php
            $db = connectToDatabase();
            $query = "SELECT * FROM users ORDER BY `date_registered` DESC LIMIT 10";
            $result = $db->query($query);
        ?>

        <table>
            <thead>
                <tr>
                    <td>User ID</td>
                    <td>Name</td>
                    <td>Email</td>
                    <td>Balance</td>
                    <td>KYC Level</td>
                    <td>Date Registered</td>
                    <td></td>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <?php 
                                $select_user_email = $row['email'];
                                $su_query = "SELECT SUM(amount) AS user_balance FROM transactions WHERE user_email = '$select_user_email'";
                                $su_result = $db->query($su_query);
                                $user_balance = $su_result->fetch_assoc()['user_balance'] ?? 0;
                            ?>
                            <td>$<?php echo htmlspecialchars(number_format($user_balance, 2)); ?></td>
                            <td><?php echo htmlspecialchars($row['kyc_level']); ?></td>
                            <td><?php echo htmlspecialchars(date('d, F Y', $row['date_registered'])); ?></td>
                            <td><a href="/control-panel/profile-picture/?id=<?php echo htmlspecialchars($row['id']); ?>">View Profile Picture</td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No users found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $db->close();
        ?>

        <a href="/control-panel/site-users" class="cta">View all users</a>
    </div>
</section>
<section class="form">
<?php
    if (isset($_POST['view_user_dashboard'])) {
        // Sanitize and retrieve the client's email from the form
        $client_email = filter_var($_POST['client_email'], FILTER_SANITIZE_EMAIL);
    
        // Set a cookie for the client's email that expires in 1 hour
        setcookie('client_email', $client_email, time() + 3600, "/");
    
        // Redirect to the client dashboard
        header("Location: /client-dashboard?client_email=" . urlencode($client_email));
        exit();
    }
    
    ?>
    <div class="container">
        <form action="" method="post">
            <h2>View User Dashboard Temporarily</h2>
            <div class="input-box">
                <label>User Email</label>
                <input type="email" name="client_email" required>
            </div>
            <div class="input-box">
                <button type="submit" name="view_user_dashboard">View User Dashboard</button>
            </div>
        </form>
    </div>
</section>
<section class="table user-accounts">
    <div class="container">
        <h2>User Accounts Short List (Max 10 Users)</h2>
        <?php
            $db = connectToDatabase();
            $query = "SELECT * FROM accounts ORDER BY `creation_time` DESC LIMIT 10";
            $result = $db->query($query);
        ?>

        <table>
            <thead>
                <tr>
                    <td>User ID</td>
                    <td>User Name</td>
                    <td>User Email</td>
                    <td>Account Number</td>
                    <td>Balance</td>
                    <td>Account Status</td>
                    <td>Date</td>
                </tr>
            </thead>
            <tbody>
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                            <?php 
                                $select_user_account = $row['account_number'];
                                $su_query = "SELECT SUM(amount) AS account_balance FROM transactions WHERE account_number = '$select_user_account'";
                                $su_result = $db->query($su_query);
                                $account_balance = $su_result->fetch_assoc()['account_balance'] ?? 0;
                                ?>
                            <td>$<?php echo htmlspecialchars(number_format($account_balance, 2)); ?></td>
                            <td><?php echo htmlspecialchars($row['account_status']); ?></td>
                            <td><?php echo htmlspecialchars(date('d, F Y', $row['creation_time'])); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7">No accounts found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php
        $db->close();
        ?>

        <a href="/control-panel/accounts" class="cta">View all accounts</a>
    </div>
</section>
<section class="form">
    <div class="container">
    <?php
        if (isset($_POST['delete_user'])) {
            // Collect and sanitize the form data
            $user_id = filter_var($_POST['id'], FILTER_VALIDATE_INT);

            if ($user_id) {
                // Database connection
                $db = connectToDatabase();

                // Prepare and execute the delete query
                $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);

                if ($stmt->execute()) {
                    // Successful deletion
                    echo "User with ID $user_id has been deleted.";
                } else {
                    // Error handling
                    echo "Error deleting user: " . $stmt->error;
                }

                // Close the statement and database connection
                $stmt->close();
                $db->close();

                // Redirect to another page (e.g., dashboard) after deletion
                header("Location: /control-panel");
                exit();
            } else {
                echo "Invalid ID provided.";
            }
        }
    ?>
        <form action="" method="post">
            <h2>Delete User</h2>
            <div class="input-box">
                <label>ID</label>
                <input type="number" name="id" required>
            </div>
            <div class="input-box">
                <button type="submit" name="delete_user">Delete User</button>
            </div>
        </form>
    </div>
</section>
<section class="form">
<?php
if (isset($_POST['delete_user_account'])) {
    // Collect and sanitize the form data
    $account_number = filter_var($_POST['account_number'], FILTER_SANITIZE_NUMBER_INT);

    if ($account_number) {
        // Database connection
        $db = connectToDatabase();

        // Prepare and execute the delete query
        $stmt = $db->prepare("DELETE FROM accounts WHERE account_number = ?");
        $stmt->bind_param("i", $account_number);

        if ($stmt->execute()) {
            // Successful deletion
            echo "Account with number $account_number has been deleted.";
        } else {
            // Error handling
            echo "Error deleting account: " . $stmt->error;
        }

        // Close the statement and database connection
        $stmt->close();
        $db->close();

        // Redirect to another page (e.g., account management) after deletion
        header("Location: /control-panel");
        exit();
    } else {
        echo "Invalid account number provided.";
    }
}
?>

    <div class="container">
    <form action="" method="post">
    <h2>Delete User Bank Account</h2>
    <div class="input-box">
        <label>Account Number</label>
        <input type="number" name="account_number" required>
    </div>
    <div class="input-box">
        <button type="submit" name="delete_user_account">Delete Account</button>
    </div>
</form>

    </div>
</section>
<section class="form deposit-user" name="Credit User">
    <div class="container">
        <form action="" method="post">
            <h3>Deposit Into User Account</h3>
            <div class="input-box">
                <label>User Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-box">
                <label>Account Number</label>
                <input type="number" name="account_number" required>
            </div>
            <div class="input-box">
                <label>Amount ($)</label>
                <input type="number" name="amount" required>
            </div>
            <div class="input-box">
                <label>Currency</label>
                <select name="currency">
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="input-box">
                <label>Description</label>
                <textarea name="description" cols="10" rows="10"></textarea>
            </div>
            <div class="input-box">
                <button type="submit" name="credit_user" value="1">Deposit</button>
            </div>
        </form>
    </div>
</section>
<section class="form withdraw-from-user" name="Debit User" style="display: none;">
    <div class="container">
        <form action="" method="post">
            <h3>Withdraw From User Account</h3>
            <div class="input-box">
                <label>User Email</label>
                <input type="email" name="email" required>
            </div>
            <div class="input-box">
                <label>Account Number</label>
                <input type="number" name="account_number" required>
            </div>
            <div class="input-box">
                <label>Amount</label>
                <input type="number" name="amount" required>
            </div>
            <div class="input-box">
                <label>Currency</label>
                <select name="currency">
                    <option value="USD">USD</option>
                </select>
            </div>
            <div class="input-box">
                <label>Description</label>
                <textarea name="description" cols="10" rows="10"></textarea>
            </div>
            <div class="input-box">
                <button type="submit" name="debit_user" value="100">Withdraw</button>
            </div>
        </form>
    </div>
</section>
<section class="table list-of-withdrawals">
    <div class="container">
        <h2>List of Transactions (10)</h2>
        <?php
            // Database connection
            $db = connectToDatabase();

            // Query to get data from transactions table
            $query = "SELECT * FROM transactions ORDER BY time DESC LIMIT 10";
            $result = $db->query($query);
            ?>

            <table>
                <thead>
                    <tr>
                        <td>ID</td>
                        <td>Transaction Type</td>
                        <td>Email</td>
                        <td>Account Number</td>
                        <td>Amount</td>
                        <td>Status</td>
                        <td>Time</td>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['type']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                <td><?php echo htmlspecialchars($row['account_number']); ?></td>
                                <td><?php echo htmlspecialchars(abs($row['amount'])); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars(date('d, F Y H:i:s /E/T', $row['time'])); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7">No transactions found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            // Close the database connection
            $db->close();
        ?>

        <a href="/control-panel/transactions" class="cta">View all transactions</a>
    </div>
</section>
<section class="form approve-withdrawal">
    <div class="container">
        <form action="" method="post">
            <h3>Approve/Reject User Withdrawal</h3>
            <div class="input-box">
                <label>ID</label>
                <input type="number" name="withdrawal_id" required>
            </div>
            <div class="input-box">
                <label>Decision</label>
                <select name="decision">
                    <option value="Completed">Approve</option>
                    <option value="Failed">Reject</option>
                </select>
            </div>
            <div class="input-box">
                <label>Description/Reason</label>
                <textarea name="description" cols="10" rows="10"></textarea>
            </div>
            <div class="input-box">
                <button type="submit" name="judge_withdrawal" value="1">Submit</button>
            </div>
        </form>
    </div>
</section>
<section class="table list-of-kyc">
    <div class="container">
        <h2>List of KYC</h2>
        <?php
            // Connect to the database
            $db = connectToDatabase();

            // Query to select data from the kyc_data table
            $query = "SELECT * FROM kyc_data ORDER BY id DESC LIMIT 10"; // Adjust ORDER BY as needed
            $result = $db->query($query);
            ?>

            <table>
                <thead>
                    <tr>
                        <td>ID</td>
                        <td>First Name</td>
                        <td>Last Name</td>
                        <td>Email</td>
                        <td>Status</td>
                        <td>Date of Birth</td>
                        <td></td>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['id']); ?></td>
                                <td><?php echo htmlspecialchars($row['first_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['last_name']); ?></td>
                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                <td><?php echo htmlspecialchars($row['status']); ?></td>
                                <td><?php echo htmlspecialchars(date('d, F Y', strtotime($row['date_of_birth']))); ?></td>
                                <td><a href="/control-panel/kyc/?id=<?php echo htmlspecialchars($row['id']); ?>">View Full Details</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">No records found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <?php
            // Close the database connection
            $db->close();
        ?>

        <a href="kyc-data" class="cta">View All KYC Data</a>
    </div>
</section>
<section class="form approve-kyc">
    <div class="container">
        <form action="" method="post">
            <h3>Approve/Reject User KYC Verificatiom</h3>
            <div class="input-box">
                <label>KYC ID</label>
                <input type="number" name="kyc_id" required>
            </div>
            <div class="input-box">
                <label>Decision</label>
                <select name="decision">
                    <option value="Approved">Approve</option>
                    <option value="Rejected">Reject</option>
                </select>
            </div>
            <div class="input-box">
                <label>Description/Reaon</label>
                <textarea name="description" cols="10" rows="10"></textarea>
            </div>
            <div class="input-box">
                <button type="submit" name="judge_kyc" value="1">Submit</button>
            </div>
        </form>
    </div>
</section>
<script src="/assets/scripts/control-panel.js?v=<?php echo time(); ?>"></script>
</body>
</html>
