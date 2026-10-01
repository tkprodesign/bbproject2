<?php require_once('../common-sections/app.php'); ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Online Banking | Velmora Bank</title><meta name="description" content="Secure online banking for account balances, transfers, beneficiaries, FX, notifications and security activity.">
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<?php include('../common-sections/public-v2-header.php'); ?>
<main>
<section class="pv2-page-hero"><div class="pv2-container pv2-hero-grid">
  <div><span class="pv2-eyebrow">VELMORA ONLINE BANKING</span><h1>A digital bank should behave like a bank, not a visual demo.</h1>
  <p>Accounts retain their actual currencies. Transfers go through review. FX uses a bank quote. Transactions keep references and status. Your profile and security activity stay connected to the same banking relationship.</p>
  <div class="pv2-actions"><a class="pv2-btn primary" href="/login-v2/">Sign in securely <span class="material-symbols-rounded">arrow_forward</span></a><a class="pv2-btn secondary" href="/signup-v2/">Open an account</a></div></div>
  <div class="pv2-hero-image"><img src="/assets/images/home/features/online-banking.jpg" alt="Online banking"><div class="pv2-image-note"><small>DIGITAL BANKING</small><strong>Accounts, transfers, FX, beneficiaries and security.</strong></div></div>
</div></section>

<section class="pv2-trust"><div class="pv2-container pv2-trust-grid">
  <div><span class="material-symbols-rounded">account_balance_wallet</span><p><strong>Real account currencies</strong><small>No instant cosmetic currency switching.</small></p></div>
  <div><span class="material-symbols-rounded">receipt_long</span><p><strong>Professional ledger</strong><small>References, channel, status and value dates.</small></p></div>
  <div><span class="material-symbols-rounded">currency_exchange</span><p><strong>Quoted FX</strong><small>Review rate and amount before execution.</small></p></div>
  <div><span class="material-symbols-rounded">shield_lock</span><p><strong>Security activity</strong><small>Recent sign-in and account events remain visible.</small></p></div>
</div></section>

<section class="pv2-section"><div class="pv2-container">
  <div class="pv2-section-head"><div><span class="pv2-eyebrow">YOUR BANKING WORKSPACE</span><h2>One place for the actions that define the relationship.</h2></div><p>The online banking product is structured around the database and the real state of each account—not static dashboard numbers.</p></div>
  <div class="pv2-card-grid">
    <article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">account_balance</span></span><h3>Accounts</h3><p>Review account type, currency, balance, status, alias and opening information.</p></article>
    <article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">sync_alt</span></span><h3>Transfers</h3><p>Prepare recipient details, review any conversion and submit the payment for processing.</p></article>
    <article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">currency_exchange</span></span><h3>Currency exchange</h3><p>Move between your own currency accounts using a time-limited bank quote and confirmation step.</p></article>
    <article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">group</span></span><h3>Beneficiaries</h3><p>Save trusted recipient details and reuse them in future payment workflows.</p></article>
    <article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">notifications</span></span><h3>Notifications</h3><p>See account, transfer and security messages inside the banking workspace.</p></article>
    <article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">verified_user</span></span><h3>Profile & security</h3><p>Review customer metadata, KYC information, preferences and recent security events.</p></article>
  </div>
</div></section>

<section class="pv2-section pv2-dark"><div class="pv2-container pv2-process">
  <div><span class="pv2-eyebrow">INTERACTION PRINCIPLE</span><h2>The screen should tell the truth about the money.</h2><p style="font-size:11px;line-height:1.8;color:#a7b6c5">A banking interface becomes credible when the visual state reflects the financial state. A UI control must not imply that money moved when no banking transaction occurred.</p></div>
  <div class="pv2-steps">
    <div class="pv2-step" style="border-color:rgba(255,255,255,.1)"><span style="color:#8fa8bf">01</span><div><strong>Account balance</strong><small style="color:#9db0c1">Comes from transactions attached to the account.</small></div></div>
    <div class="pv2-step" style="border-color:rgba(255,255,255,.1)"><span style="color:#8fa8bf">02</span><div><strong>Transfer status</strong><small style="color:#9db0c1">Shows whether activity is pending, successful or failed.</small></div></div>
    <div class="pv2-step" style="border-color:rgba(255,255,255,.1)"><span style="color:#8fa8bf">03</span><div><strong>FX execution</strong><small style="color:#9db0c1">Creates debit and credit ledger entries after confirmation.</small></div></div>
    <div class="pv2-step" style="border-color:rgba(255,255,255,.1)"><span style="color:#8fa8bf">04</span><div><strong>Security events</strong><small style="color:#9db0c1">Recorded sign-in and preference activity supports account review.</small></div></div>
  </div>
</div></section>

<section class="pv2-section"><div class="pv2-container">
  <div class="pv2-section-head"><div><span class="pv2-eyebrow">DIGITAL EXPERIENCE</span><h2>Built for both quick checks and serious banking tasks.</h2></div></div>
  <div class="pv2-feature-table">
    <div class="pv2-feature-row"><strong>Overview</strong><span>See primary account information, recent activity and customer relationship details.</span></div>
    <div class="pv2-feature-row"><strong>Transaction history</strong><span>Search and review ledger activity with references and value dates.</span></div>
    <div class="pv2-feature-row"><strong>FX trade history</strong><span>Review currencies sold and bought, rate and execution status.</span></div>
    <div class="pv2-feature-row"><strong>Preferences</strong><span>Manage selected alert and statement-delivery preferences.</span></div>
  </div>
</div></section>

<section class="pv2-cta"><div class="pv2-container"><div><span class="pv2-eyebrow" style="color:#8fa8bf">ONLINE BANKING</span><h2>Access your Velmora banking workspace.</h2></div><div><a class="pv2-btn gold" href="/login-v2/">Sign in</a><a class="pv2-btn secondary" href="/signup-v2/">Open an account</a></div></div></section>
</main>
<?php include('../common-sections/public-v2-footer.php'); ?></body></html>