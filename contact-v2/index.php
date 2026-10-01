<?php
require_once('../common-sections/app.php');
$contactSuccess=false;$contactError='';
if(isset($_POST['contact_submit'])){
  $fullName=trim((string)($_POST['full_name']??''));$fromEmail=trim((string)($_POST['email']??''));$subject=trim((string)($_POST['subject']??''));$message=trim((string)($_POST['message']??''));
  if($fullName!==''&&filter_var($fromEmail,FILTER_VALIDATE_EMAIL)&&$subject!==''&&$message!==''){
    $safeName=htmlspecialchars($fullName,ENT_QUOTES,'UTF-8');$safeEmail=htmlspecialchars($fromEmail,ENT_QUOTES,'UTF-8');$safeSubject=htmlspecialchars($subject,ENT_QUOTES,'UTF-8');$safeMessage=nl2br(htmlspecialchars($message,ENT_QUOTES,'UTF-8'));
    $emailSubject='Contact Form: '.$subject;
    $body='<!doctype html><html><body style="font-family:Arial,sans-serif;background:#f3f6f8;padding:24px"><div style="max-width:640px;margin:auto;background:#fff;border:1px solid #e4e9ef;border-radius:12px;overflow:hidden"><div style="background:#0b2239;padding:20px;color:#fff"><strong>Velmora Bank — Contact Request</strong></div><div style="padding:24px"><p><strong>Name:</strong> '.$safeName.'</p><p><strong>Email:</strong> '.$safeEmail.'</p><p><strong>Subject:</strong> '.$safeSubject.'</p><p><strong>Message:</strong><br>'.$safeMessage.'</p></div></div></body></html>';
    if(sendSiteEmail('support@velmorabank.us',$emailSubject,$body)){$contactSuccess=true;}else{$contactError='Your message could not be sent. Please use the published email or phone channel.';}
  }else{$contactError='Please complete all required fields with valid information.';}
}
$supportPhoneNumber=getSupportPhoneNumber();$supportWhatsappLink=getSupportWhatsappLink();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Contact Velmora | Velmora Bank</title><meta name="description" content="Contact Velmora Bank for account, transfer, lending, card, security and general banking support.">
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<?php include('../common-sections/public-v2-header.php'); ?>
<main>
<section class="pv2-page-hero"><div class="pv2-container pv2-hero-grid">
<div><span class="pv2-eyebrow">CONTACT VELMORA</span><h1>Tell us what you need help with.</h1><p>Use the contact form for general requests, or choose a direct channel for account access, security concerns and time-sensitive banking issues.</p></div>
<div class="pv2-hero-image"><img src="/assets/images/home/contact-2.jpg" alt="Velmora customer support"><div class="pv2-image-note"><small>CLIENT SUPPORT</small><strong>Account questions · transfers · lending · security · profile</strong></div></div>
</div></section>

<section class="pv2-section pv2-soft"><div class="pv2-container pv2-contact-grid">
<div class="pv2-form-card"><span class="pv2-eyebrow">SEND A MESSAGE</span><h2>General support request</h2><p>Do not include passwords or one-time security codes. For suspected fraud or account compromise, use the Security & Fraud Center guidance and contact support directly.</p>
<?php if($contactSuccess): ?><div class="pv2-alert success">Your message was sent successfully. Support can follow up using the contact information you provided.</div><?php elseif($contactError!==''): ?><div class="pv2-alert error"><?php echo htmlspecialchars($contactError); ?></div><?php endif; ?>
<?php if(!$contactSuccess): ?><form method="post" class="pv2-form">
<label><span>Full name</span><input type="text" name="full_name" required value="<?php echo htmlspecialchars($_POST['full_name']??'',ENT_QUOTES,'UTF-8'); ?>"></label>
<label><span>Email address</span><input type="email" name="email" required value="<?php echo htmlspecialchars($_POST['email']??'',ENT_QUOTES,'UTF-8'); ?>"></label>
<label><span>Subject</span><input type="text" name="subject" required placeholder="What do you need help with?" value="<?php echo htmlspecialchars($_POST['subject']??'',ENT_QUOTES,'UTF-8'); ?>"></label>
<label><span>Message</span><textarea class="pv2-textarea" name="message" required placeholder="Include the useful context, but never send passwords or security codes."><?php echo htmlspecialchars($_POST['message']??'',ENT_QUOTES,'UTF-8'); ?></textarea></label>
<button class="pv2-btn primary" type="submit" name="contact_submit" value="1">Submit request</button>
</form><?php endif; ?></div>

<aside class="pv2-info-card"><span class="pv2-eyebrow">DIRECT CHANNELS</span><h2>Contact details</h2>
<div class="pv2-info-list">
<div><span class="material-symbols-rounded">mail</span><div><strong>Email</strong><a href="mailto:support@velmorabank.us">support@velmorabank.us</a></div></div>
<div><span class="material-symbols-rounded">call</span><div><strong>Phone / WhatsApp</strong><a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($supportPhoneNumber); ?></a></div></div>
<div><span class="material-symbols-rounded">location_on</span><div><strong>Address</strong><small>400 Park Ave, New York, NY 10022, United States</small></div></div>
<div><span class="material-symbols-rounded">schedule</span><div><strong>Published service hours</strong><small>Monday–Friday · 8:00 AM–8:00 PM EST</small></div></div>
</div>
<div class="pv2-actions"><a class="pv2-btn secondary" href="/support-v2/">Support Center</a><a class="pv2-btn secondary" href="/security-v2/">Security & Fraud</a></div>
</aside>
</div></section>

<section class="pv2-section"><div class="pv2-container">
<div class="pv2-section-head"><div><span class="pv2-eyebrow">ROUTE THE ISSUE CORRECTLY</span><h2>Different banking problems need different context.</h2></div></div>
<div class="pv2-card-grid">
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">receipt_long</span></span><h3>Transaction question</h3><p>Include the transaction reference, date, account ending and what you expected to happen.</p></article>
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">shield</span></span><h3>Security concern</h3><p>If credentials may be exposed or activity is unfamiliar, secure the account first and contact support immediately.</p><a href="/security-v2/">Security guidance <span class="material-symbols-rounded">arrow_forward</span></a></article>
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">person</span></span><h3>Profile / KYC change</h3><p>Explain what verified information needs to change so support can guide the appropriate review process.</p></article>
</div>
</div></section>
</main>
<?php include('../common-sections/public-v2-footer.php'); ?></body></html>