<?php require_once('../common-sections/app.php'); $supportPhoneNumber=getSupportPhoneNumber(); $supportWhatsappLink=getSupportWhatsappLink(); ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Support Center | Velmora Bank</title><meta name="description" content="Help with Velmora accounts, transfers, FX, access, profile information and security.">
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<?php include('../common-sections/public-v2-header.php'); ?>
<main>
<section class="pv2-page-hero"><div class="pv2-container pv2-hero-grid">
<div><span class="pv2-eyebrow">SUPPORT CENTER</span><h1>Start with the issue. Get to the right help faster.</h1><p>Find guidance for accounts, payments, currency exchange, profile verification and online banking access, or contact support when the issue needs direct attention.</p><div class="pv2-actions"><a class="pv2-btn primary" href="/dashboard/support/">Signed-in support messages</a><a class="pv2-btn secondary" href="/contact/">General contact</a></div></div>
<div class="pv2-hero-image"><img src="/assets/images/home/features/customer-support.jpg" alt="Velmora support"><div class="pv2-image-note"><small>SUPPORT</small><strong>Accounts · payments · access · security · verification</strong></div></div>
</div></section>

<section class="pv2-section"><div class="pv2-container">
<div class="pv2-section-head"><div><span class="pv2-eyebrow">HELP BY TOPIC</span><h2>Choose what you are trying to resolve.</h2></div></div>
<div class="pv2-card-grid">
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">account_balance</span></span><h3>Accounts</h3><p>Account opening, balances, currencies, account status and account information.</p></article>
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">payments</span></span><h3>Transfers</h3><p>Preparing a transfer, recipient details, pending status and transaction references.</p></article>
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">currency_exchange</span></span><h3>Currency exchange</h3><p>Source and destination accounts, bank quotes, FX rate review and trade history.</p></article>
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">person</span></span><h3>Profile & KYC</h3><p>Personal information, occupation, identity verification and profile change requests.</p></article>
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">lock</span></span><h3>Access & password</h3><p>Sign-in problems, password changes and questions about recent security activity.</p></article>
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">shield</span></span><h3>Fraud & security</h3><p>Suspicious messages, unfamiliar activity or concerns about account access.</p><a href="/security/">Security & Fraud Center <span class="material-symbols-rounded">arrow_forward</span></a></article>
</div>
</div></section>

<section class="pv2-section pv2-soft"><div class="pv2-container pv2-contact-grid">
<div class="pv2-info-card"><span class="pv2-eyebrow">DIRECT SUPPORT</span><h2>Need a person?</h2><p>Use the channel that fits the issue. Never send passwords or one-time security codes through a support message.</p>
<div class="pv2-info-list">
<div><span class="material-symbols-rounded">mail</span><div><strong>Email support</strong><a href="mailto:support@velmorabank.us">support@velmorabank.us</a></div></div>
<div><span class="material-symbols-rounded">call</span><div><strong>Phone / WhatsApp</strong><a href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($supportPhoneNumber); ?></a></div></div>
<div><span class="material-symbols-rounded">chat</span><div><strong>Authenticated support</strong><a href="/dashboard/support/">Open a traceable support case after signing in</a></div></div>
<div><span class="material-symbols-rounded">location_on</span><div><strong>Locations</strong><a href="/atm-and-bank-locations/">Confirm service-location information</a></div></div>
</div></div>
<div class="pv2-info-card"><span class="pv2-eyebrow">BEFORE YOU CONTACT US</span><h2>Have the useful context ready.</h2><div class="pv2-bullet-list">
<div class="pv2-bullet"><span class="material-symbols-rounded">check_circle</span><div><strong>Transaction reference</strong><small>Use the reference shown in your transaction history when available.</small></div></div>
<div class="pv2-bullet"><span class="material-symbols-rounded">check_circle</span><div><strong>Account ending</strong><small>Share the last four digits instead of unnecessary full account details.</small></div></div>
<div class="pv2-bullet"><span class="material-symbols-rounded">check_circle</span><div><strong>What you expected vs what happened</strong><small>A concise explanation helps support understand the issue faster.</small></div></div>
</div></div>
</div></section>

<section class="pv2-section"><div class="pv2-container"><div class="pv2-section-head"><div><span class="pv2-eyebrow">COMMON QUESTIONS</span><h2>Quick answers before you escalate.</h2></div></div>
<div class="pv2-faq">
<article><h3>Why is a transfer still pending?</h3><p>Pending means the transfer has been recorded but has not yet reached its final processing state. Use the reference when contacting support.</p></article>
<article><h3>Can I change an account’s currency?</h3><p>No. An account keeps its denomination. Open another supported currency account and use the FX workflow to move funds.</p></article>
<article><h3>Why did my FX quote expire?</h3><p>Quotes are time-limited. Request a fresh quote so the terms you review are the terms available for confirmation.</p></article>
<article><h3>Where can I review security activity?</h3><p>Signed-in clients can use the Security area of the professional banking dashboard to review recorded events.</p></article>
</div></div></section>
</main>
<?php include('../common-sections/public-v2-footer.php'); ?></body></html>