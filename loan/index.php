<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Loan Application | Velmora Bank</title>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="stylesheet" href="/assets/stylesheets/desktop/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/desktop/marketing-pages.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/marketing-pages.css?v=<?php echo time(); ?>">
</head>
<body>
<?php include('../common-sections/header.php'); ?>
<?php
$salary = isset($_GET['salary']) ? (float)$_GET['salary'] : 0;
$amount = isset($_GET['amount']) ? (float)$_GET['amount'] : 0;
$fee = isset($_GET['fee']) ? (float)$_GET['fee'] : 0;
$tenor = isset($_GET['tenor']) ? (int)$_GET['tenor'] : 30;
$repayment = isset($_GET['repayment']) ? (float)$_GET['repayment'] : 0;
$installment = isset($_GET['installment']) ? (float)$_GET['installment'] : 0;
?>

<section class="page-hero">
    <div class="container">
        <h1>Loan Application</h1>
        <p>Review your estimated terms below and submit a request for assessment by our lending team.</p>
    </div>
</section>

<section class="page-content">
    <div class="container" style="max-width: 860px;">
        <form method="post" action="/contact/" style="display:grid; gap:16px; background:#fff; border:1px solid #e8eef7; border-radius:14px; padding:24px; box-shadow:0 8px 28px rgba(17,42,78,.08);">
            <h2 style="margin-bottom:6px;">Pre-Filled Loan Details</h2>
            <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px;">
                <label>Monthly Salary
                    <input type="text" name="monthly_salary" value="$<?php echo number_format($salary, 2); ?>" readonly>
                </label>
                <label>Requested Amount
                    <input type="text" name="requested_amount" value="$<?php echo number_format($amount, 2); ?>" readonly>
                </label>
                <label>Processing Fee
                    <input type="text" name="processing_fee" value="$<?php echo number_format($fee, 2); ?>" readonly>
                </label>
                <label>Tenor (Days)
                    <input type="text" name="tenor_days" value="<?php echo htmlspecialchars((string)$tenor); ?>" readonly>
                </label>
                <label>Total Repayment
                    <input type="text" name="total_repayment" value="$<?php echo number_format($repayment, 2); ?>" readonly>
                </label>
                <label>Estimated Monthly Installment
                    <input type="text" name="estimated_installment" value="$<?php echo number_format($installment, 2); ?>" readonly>
                </label>
            </div>

            <label>Full Name
                <input type="text" name="full_name" placeholder="Enter your full name" required>
            </label>
            <label>Email Address
                <input type="email" name="email" placeholder="Enter your email" required>
            </label>
            <label>Phone Number
                <input type="tel" name="phone" placeholder="Enter your phone number" required>
            </label>
            <label>Purpose of Loan
                <textarea name="loan_purpose" rows="4" placeholder="Tell us why you need this loan" required></textarea>
            </label>

            <button type="submit" style="padding:12px 18px; background:#163555; color:#fff; border-radius:10px; width:max-content;">Submit Loan Request</button>
        </form>
    </div>
</section>

<?php include('../common-sections/footer.php'); ?>
</body>
</html>
