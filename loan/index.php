<?php
require_once('../common-sections/app.php');

$allowedTenors = [14, 30, 60];

function sanitizeMoneyInput(string $key): ?float {
    if (!isset($_GET[$key])) {
        return null;
    }

    $rawValue = trim((string)$_GET[$key]);
    if ($rawValue === '') {
        return null;
    }

    if (!preg_match('/^\d+(?:\.\d{1,2})?$/', $rawValue)) {
        return null;
    }

    return (float)$rawValue;
}

$salary = sanitizeMoneyInput('salary');
$amount = sanitizeMoneyInput('amount');
$fee = sanitizeMoneyInput('fee');
$repayment = sanitizeMoneyInput('repayment');
$installment = sanitizeMoneyInput('installment');
$tenorRaw = isset($_GET['tenor']) ? trim((string)$_GET['tenor']) : '';
$tenor = ctype_digit($tenorRaw) ? (int)$tenorRaw : null;

$hasValidNumbers = $salary !== null && $amount !== null && $fee !== null && $repayment !== null && $installment !== null;
$hasValidTenor = $tenor !== null && in_array($tenor, $allowedTenors, true);
$principalLimit = $salary !== null ? min($salary * 0.4, 12000) : null;
$expectedFeeRate = ($tenor === 14) ? 0.08 : (($tenor === 30) ? 0.12 : (($tenor === 60) ? 0.18 : null));
$expectedFee = ($principalLimit !== null && $expectedFeeRate !== null) ? $principalLimit * $expectedFeeRate : null;
$expectedRepayment = ($principalLimit !== null && $expectedFee !== null) ? $principalLimit + $expectedFee : null;
$expectedInstallment = ($expectedRepayment !== null && $tenor !== null && $tenor > 0) ? ($expectedRepayment / $tenor) * 30 : null;
$roundEquals = static function (?float $left, ?float $right): bool {
    return $left !== null && $right !== null && abs($left - $right) < 0.02;
};

$isValidPrefill = $hasValidNumbers
    && $hasValidTenor
    && $salary > 0
    && $amount > 0
    && $fee >= 0
    && $repayment > 0
    && $installment >= 0
    && $roundEquals($amount, $principalLimit)
    && $roundEquals($fee, $expectedFee)
    && $roundEquals($repayment, $expectedRepayment)
    && $roundEquals($installment, $expectedInstallment);

if (!$isValidPrefill) {
    header('Location: /?loan_flow=invalid');
    exit;
}

$applicantData = [
    'full_name' => '',
    'email' => '',
    'phone' => '',
    'loan_purpose' => '',
];
$formErrors = [];
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicantData['full_name'] = trim((string)($_POST['full_name'] ?? ''));
    $applicantData['email'] = trim((string)($_POST['email'] ?? ''));
    $applicantData['phone'] = trim((string)($_POST['phone'] ?? ''));
    $applicantData['loan_purpose'] = trim((string)($_POST['loan_purpose'] ?? ''));

    if ($applicantData['full_name'] === '' || mb_strlen($applicantData['full_name']) < 2 || !preg_match('/^[\p{L} .\'-]+$/u', $applicantData['full_name'])) {
        $formErrors['full_name'] = 'Please enter your full legal name.';
    }

    if (!filter_var($applicantData['email'], FILTER_VALIDATE_EMAIL)) {
        $formErrors['email'] = 'Please enter a valid email address.';
    }

    $digitsOnlyPhone = preg_replace('/\D+/', '', $applicantData['phone']);
    if ($digitsOnlyPhone === '' || strlen($digitsOnlyPhone) < 7 || strlen($digitsOnlyPhone) > 15) {
        $formErrors['phone'] = 'Please enter a valid phone number.';
    }

    if ($applicantData['loan_purpose'] === '' || mb_strlen($applicantData['loan_purpose']) < 10) {
        $formErrors['loan_purpose'] = 'Please describe your loan purpose in at least 10 characters.';
    }

    if (empty($formErrors)) {
        $successMessage = 'Application captured successfully. Our lending team will contact you after review.';
        $applicantData = ['full_name' => '', 'email' => '', 'phone' => '', 'loan_purpose' => ''];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Loan Application | Velmora Bank</title>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="stylesheet" href="/assets/stylesheets/desktop/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/desktop/marketing-pages.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/marketing-pages.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/marketing-pages.css?v=<?php echo time(); ?>">
    <style>
        .loan-application-shell { max-width: 940px; display: grid; gap: 18px; }
        .loan-summary, .loan-form-card { background:#fff; border:1px solid #e8eef7; border-radius:14px; box-shadow:0 8px 28px rgba(17,42,78,.08); }
        .loan-summary { padding: 22px; }
        .loan-summary h2 { margin-bottom: 12px; }
        .loan-summary-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr)); gap:12px; }
        .loan-summary-item { border:1px solid #edf2f9; border-radius:10px; padding:12px; }
        .loan-summary-item span { display:block; color:#59718a; font-size:13px; margin-bottom:4px; }
        .loan-summary-item strong { color:#163555; font-size:18px; }
        .loan-form-card { padding:24px; }
        .loan-form-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(240px,1fr)); gap:14px; }
        .loan-form-field { display:grid; gap:6px; }
        .loan-form-field label { font-weight:600; color:#183a5b; }
        .loan-form-field input, .loan-form-field textarea { border:1px solid #d6e1ee; border-radius:10px; padding:11px 12px; font: inherit; }
        .loan-form-field textarea { resize:vertical; min-height:110px; }
        .field-error { color:#b93434; font-size:13px; }
        .loan-submit { padding:12px 18px; background:#163555; color:#fff; border-radius:10px; border:none; width:max-content; cursor:pointer; }
        .loan-success { border:1px solid #c7e9d8; background:#eefaf3; color:#1f6f43; padding:12px; border-radius:10px; }
    </style>
</head>
<body>
<?php include('../common-sections/header.php'); ?>
<section class="page-hero">
    <div class="container">
        <h1>Loan Application</h1>
        <p>Review your estimated terms below and submit a request for assessment by our lending team.</p>
    </div>
</section>

<section class="page-content">
    <div class="container loan-application-shell">
        <div class="loan-summary">
            <h2>Pre-Filled Loan Summary</h2>
            <div class="loan-summary-grid">
                <div class="loan-summary-item"><span>Monthly Salary</span><strong>$<?php echo number_format($salary, 2); ?></strong></div>
                <div class="loan-summary-item"><span>Requested Amount</span><strong>$<?php echo number_format($amount, 2); ?></strong></div>
                <div class="loan-summary-item"><span>Processing Fee</span><strong>$<?php echo number_format($fee, 2); ?></strong></div>
                <div class="loan-summary-item"><span>Tenor</span><strong><?php echo htmlspecialchars((string)$tenor, ENT_QUOTES, 'UTF-8'); ?> days</strong></div>
                <div class="loan-summary-item"><span>Total Repayment</span><strong>$<?php echo number_format($repayment, 2); ?></strong></div>
                <div class="loan-summary-item"><span>Est. Monthly Installment</span><strong>$<?php echo number_format($installment, 2); ?></strong></div>
            </div>
        </div>

        <form method="post" action="" class="loan-form-card" novalidate>
            <h2 style="margin-bottom: 12px;">Applicant Information</h2>
            <?php if ($successMessage !== ''): ?>
                <div class="loan-success"><?php echo htmlspecialchars($successMessage, ENT_QUOTES, 'UTF-8'); ?></div>
            <?php endif; ?>
            <div class="loan-form-grid">
                <div class="loan-form-field">
                    <label for="full_name">Full Name</label>
                    <input id="full_name" type="text" name="full_name" placeholder="Enter your legal full name" required value="<?php echo htmlspecialchars($applicantData['full_name'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($formErrors['full_name'])): ?><span class="field-error"><?php echo htmlspecialchars($formErrors['full_name'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                </div>
                <div class="loan-form-field">
                    <label for="email">Email Address</label>
                    <input id="email" type="email" name="email" placeholder="you@example.com" required value="<?php echo htmlspecialchars($applicantData['email'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($formErrors['email'])): ?><span class="field-error"><?php echo htmlspecialchars($formErrors['email'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                </div>
                <div class="loan-form-field">
                    <label for="phone">Phone Number</label>
                    <input id="phone" type="tel" name="phone" placeholder="Enter your phone number" required value="<?php echo htmlspecialchars($applicantData['phone'], ENT_QUOTES, 'UTF-8'); ?>">
                    <?php if (isset($formErrors['phone'])): ?><span class="field-error"><?php echo htmlspecialchars($formErrors['phone'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                </div>
                <div class="loan-form-field" style="grid-column: 1 / -1;">
                    <label for="loan_purpose">Purpose of Loan</label>
                    <textarea id="loan_purpose" name="loan_purpose" rows="4" placeholder="Briefly tell us how you plan to use this loan" required><?php echo htmlspecialchars($applicantData['loan_purpose'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    <?php if (isset($formErrors['loan_purpose'])): ?><span class="field-error"><?php echo htmlspecialchars($formErrors['loan_purpose'], ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
                </div>
            </div>
            <button type="submit" class="loan-submit">Submit Loan Request</button>
        </form>
    </div>
</section>

<?php include('../common-sections/footer.php'); ?>
</body>
</html>
