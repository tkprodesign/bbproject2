<?php
require_once('../common-sections/app.php');

$contactSuccess = false;
$contactError = '';

if (isset($_POST['contact_submit'])) {
    $fullName    = trim(htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8'));
    $fromEmail   = trim(filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL));
    $subject     = trim(htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8'));
    $message     = trim(htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'));

    if ($fullName && filter_var($fromEmail, FILTER_VALIDATE_EMAIL) && $subject && $message) {
        $emailSubject  = 'Contact Form: ' . $subject;
        $introHtml     = '<p style="margin:0 0 8px 0;">You have received a new message from the contact form on <strong>velmorabank.us</strong>.</p>';
        $detailsHtml   = '
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0"
                   style="border:1px solid #e2e8f2;border-radius:8px;background:#ffffff;">
                <tr>
                    <td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Name</td>
                    <td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . $fullName . '</td>
                </tr>
                <tr>
                    <td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Email</td>
                    <td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . $fromEmail . '</td>
                </tr>
                <tr>
                    <td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:13px;color:#6f8199;">Subject</td>
                    <td style="padding:12px 16px;border-bottom:1px solid #eef2f7;font-size:14px;color:#0f2742;font-weight:700;text-align:right;">' . $subject . '</td>
                </tr>
                <tr>
                    <td style="padding:12px 16px;font-size:13px;color:#6f8199;vertical-align:top;">Message</td>
                    <td style="padding:12px 16px;font-size:14px;color:#0f2742;text-align:right;">' . nl2br($message) . '</td>
                </tr>
            </table>';

        $logoUrl   = 'https://velmorabank.us/assets/images/branding/logo.png';
        $emailBody = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><title>' . $emailSubject . '</title></head>
            <body style="margin:0;padding:0;background:#f3f6fb;font-family:Arial,Helvetica,sans-serif;color:#1a2b44;">
            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6fb;padding:24px 0;">
            <tr><td align="center">
            <table role="presentation" width="640" cellspacing="0" cellpadding="0" border="0"
                   style="width:640px;max-width:94%;background:#fff;border:1px solid #e4e9f2;border-radius:12px;overflow:hidden;">
                <tr><td style="background:#0f2742;padding:22px 28px;">
                    <img src="' . $logoUrl . '" alt="Velmora Bank" style="height:36px;width:auto;display:block;">
                </td></tr>
                <tr><td style="padding:28px 32px 8px 32px;">
                    <p style="margin:0 0 8px 0;font-size:12px;letter-spacing:.08em;color:#6f8199;text-transform:uppercase;">Contact Form Submission</p>
                    <h1 style="margin:0;font-size:22px;color:#0f2742;">New Message Received</h1>
                </td></tr>
                <tr><td style="padding:12px 32px 10px 32px;font-size:15px;line-height:1.7;color:#3a4a62;">' . $introHtml . '</td></tr>
                <tr><td style="padding:6px 32px 24px 32px;">' . $detailsHtml . '</td></tr>
                <tr><td style="padding:18px 32px;background:#f8faff;border-top:1px solid #e4e9f2;">
                    <p style="margin:0;font-size:12px;color:#6f8199;">Velmora Bank, 400 Park Ave, New York, NY 10022</p>
                </td></tr>
            </table>
            </td></tr></table></body></html>';

        if (sendSiteEmail('support@velmorabank.us', $emailSubject, $emailBody)) {
            $contactSuccess = true;
        } else {
            $contactError = 'Your message could not be sent. Please email us directly at support@velmorabank.us.';
        }
    } else {
        $contactError = 'Please fill in all required fields with valid information.';
    }
}

$supportPhoneNumber = getSupportPhoneNumber();
$supportWhatsappLink = getSupportWhatsappLink();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Contact Us | Velmora Bank</title>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="stylesheet" href="/assets/stylesheets/desktop/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/stylesheets/desktop/marketing-pages.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/marketing-pages.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/marketing-pages.css?v=<?php echo time(); ?>">
</head>
<body>
<?php include('../common-sections/header.php'); ?>

<section class="page-hero">
    <div class="container">
        <h1>Contact Velmora Bank</h1>
        <p>Reach us for account support, lending help, card assistance, and branch information.</p>
    </div>
</section>

<section class="contact-wrap">
    <div class="box">
        <h2>Send us a message</h2>

        <?php if ($contactSuccess): ?>
            <div class="contact-success">
                <p>&#10003; Your message was sent successfully. We'll be in touch shortly.</p>
            </div>
        <?php else: ?>
            <?php if ($contactError): ?>
                <p class="contact-error"><?php echo $contactError; ?></p>
            <?php endif; ?>
            <form action="" method="post">
                <input type="text" name="full_name" placeholder="Full Name"
                       value="<?php echo htmlspecialchars($_POST['full_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                <input type="email" name="email" placeholder="Email Address"
                       value="<?php echo htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                <input type="text" name="subject" placeholder="Subject"
                       value="<?php echo htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" required>
                <textarea name="message" rows="5" placeholder="How can we help?" required><?php echo htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                <button type="submit" name="contact_submit" value="1">Submit Request</button>
            </form>
        <?php endif; ?>
    </div>

    <div class="box">
        <h2>Contact details</h2>
        <p><strong>Email:</strong> support@velmorabank.us</p>
        <p><strong>Phone:</strong>
            <a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener">
                <?php echo htmlspecialchars($supportPhoneNumber); ?>
            </a>
        </p>
        <p><strong>Address:</strong> 400 Park Ave, New York, NY 10022, United States</p>
        <p><strong>Service Hours:</strong> Monday - Friday, 8:00 AM to 8:00 PM EST</p>
    </div>
</section>

<?php include('../common-sections/footer.php'); ?>
</body>
</html>
