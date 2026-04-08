<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow"><title>Contact Us | Velmora Bank</title><link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png"><link rel="stylesheet" href="/assets/stylesheets/desktop/main.css?v=<?php echo time(); ?>">
    <link rel="shortcut icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="apple-touch-icon" href="/assets/images/branding/velmora/icon.png">
    <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/main.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" media="screen and (max-width: 720px)" href="/assets/stylesheets/mobile/main.css?v=<?php echo time(); ?>"><link rel="stylesheet" href="/assets/stylesheets/desktop/marketing-pages.css?v=<?php echo time(); ?>">
  <link rel="stylesheet" media="screen and (max-width: 1000px)" href="/assets/stylesheets/tab/marketing-pages.css?v=<?php echo time(); ?>"></head><body>
<?php
require_once('../common-sections/app.php');
$supportPhoneNumber = getSupportPhoneNumber();
$supportWhatsappLink = getSupportWhatsappLink();
include('../common-sections/header.php');
?>
<section class="page-hero"><div class="container"><h1>Contact Velmora Bank</h1><p>Reach us for account support, lending help, card assistance, and branch information.</p></div></section>
<section class="contact-wrap">
  <div class="box"><h2>Send us a message</h2><form action="#" method="post"><input type="text" placeholder="Full Name" required><input type="email" placeholder="Email Address" required><input type="text" placeholder="Subject" required><textarea rows="5" placeholder="How can we help?" required></textarea><button type="submit">Submit Request</button></form></div>
  <div class="box"><h2>Contact details</h2><p><strong>Email:</strong> support@velmorabank.us</p><p><strong>Phone:</strong> <a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($supportPhoneNumber); ?></a></p><p><strong>Address:</strong> 400 Park Ave, New York, NY 10022, United States</p><p><strong>Service Hours:</strong> Monday - Friday, 8:00 AM to 8:00 PM EST</p></div>
</section>
<?php include('../common-sections/footer.php'); ?>
</body></html>
