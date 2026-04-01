<?php include('../../app.php');?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/assets/stylesheets/dashboard.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/tab/dashboard.css?v=<?php echo time(); ?>" media="screen and (max-width: 1000px)">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <script src="https://kit.fontawesome.com/79b279a6c9.js" crossorigin="anonymous"></script>
    <script type="text/javascript">
        var _smartsupp = _smartsupp || {};
        _smartsupp.key = '598556b50d3bd3f128d2c9362dccbf940458225d';
        window.smartsupp||(function(d) {
        var s,c,o=smartsupp=function(){ o._.push(arguments)};o._=[];
        s=d.getElementsByTagName('script')[0];c=d.createElement('script');
        c.type='text/javascript';c.charset='utf-8';c.async=true;
        c.src='https://www.smartsuppchat.com/loader.js?';s.parentNode.insertBefore(c,s);
        })(document);
    </script>
<noscript> Powered by <a href=“https://www.smartsupp.com” target=“_blank”>Smartsupp</a></noscript>
</head>
<body>
<?php include('../../../common-sections/dashboard-header.html')?>
<?php if(isset($user_kyc_level) && $user_kyc_level == 0) :?>
    <section class="kyc-ver">
        <div class="content">
            <img src="/assets/images/kyc-ver-placeholder.png">
            <h1>PENDING KYC VERIFICATION</h1>
            <p>Your KYC Verification hasn't been processed. You can only send money when your kyc verification has passed.</p>
        </div>
    </section>
<?php else: ?>
    <section class="add-account" name="user withdraw">
        <div class="container">
            <!-- <a href="#" class="manage-accounts">Manage Accounts</a> -->
            <form action="" method="post">
                <h2>Transfer</h2>
                <div class="input-box">
                    <label>Bank Name</label>
                    <input type="text" name="bank_name"  class="dark-bg">
                </div>
                <div class="input-box">
                    <label>Account Number</label>
                    <input type="number" name="account_number"  class="dark-bg">
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
                    <label>Currency</label>
                    <select name="currency">
                        <option value disabled selected>Choose</option>
                        <option value="USD">USD</option>
                    </select>
                </div>
                <div class="input-box">
                    <label>Amount</label>
                    <input type="number" name="amount">
                </div>
                <div class="input-box">
                    <label>From Account</label>
                    <?php
                        // Assume $user_email is already set and sanitized
                        $user_email = $_SESSION['user_email']; // or however you get the user's email

                        // Database connection
                        $db = connectToDatabase();

                        // Query the accounts table
                        $stmt = $db->prepare("SELECT account_number, account_type FROM accounts WHERE user_email = ?");
                        $stmt->bind_param("s", $user_email);
                        $stmt->execute();
                        $result = $stmt->get_result();

                        // Check if any rows are returned
                        if ($result->num_rows > 0) {
                            echo '<select name="from_account">';
                            while ($row = $result->fetch_assoc()) {
                                $account_number = htmlspecialchars($row['account_number']); // sanitize output
                                $account_type = htmlspecialchars($row['account_type']); // sanitize output
                                echo '<option value="' . $account_number . '-' . $account_type .'">' . $account_number . ' - ' . $account_type . '</option>';
                            }
                            echo '</select>';
                        } else {
                            echo '<select name="from_account" disabled>';
                            echo '<option value="" disabled selected>empty</option>';
                            echo '</select>';
                        }

                        // Close the statement and database connection
                        $stmt->close();
                        $db->close();
                        ?>


                </div>
                <div class="input-box">
                    <button type="submit" name="transfer_funds" value="1">Transfer</button>
                </div>
            </form>
        </div>
    </section>
<?php endif; ?>
<script src="/assets/scripts/dashboard.js?v=<?php echo time(); ?>"></script>
</body>
</html>