<?php require_once('../common-sections/app.php'); $supportPhoneNumber=getSupportPhoneNumber(); $supportWhatsappLink=getSupportWhatsappLink(); ?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Security & Fraud Center | Velmora Bank</title><meta name="description" content="Security guidance for suspicious activity, impersonation attempts, passwords and account protection.">
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<?php include('../common-sections/public-v2-header.php'); ?>
<main>
<section class="pv2-page-hero"><div class="pv2-container pv2-hero-grid">
<div><span class="pv2-eyebrow">SECURITY &amp; FRAUD CENTER</span><h1>If something feels wrong, treat it as a security issue first.</h1><p>Learn what Velmora will not ask you for, how to react to suspicious contact and where to report unfamiliar account activity.</p><div class="pv2-actions"><a class="pv2-btn primary" href="/contact-v2/">Report a concern</a><a class="pv2-btn secondary" href="/login/">Review your account</a></div></div>
<div class="pv2-hero-image"><img src="/assets/images/home/benefits/financial-security.jpg" alt="Banking security"><div class="pv2-image-note"><small>ACCOUNT SECURITY</small><strong>Verify the channel before you share information or move money.</strong></div></div>
</div></section>

<section class="pv2-section"><div class="pv2-container">
<div class="pv2-warning"><span class="material-symbols-rounded">warning</span><div><strong>Velmora support should not need your full password or a one-time security code.</strong><p>Do not provide credentials or allow remote access to your device because someone claims to be from the bank.</p></div></div>
</div></section>

<section class="pv2-section pv2-soft"><div class="pv2-container">
<div class="pv2-section-head"><div><span class="pv2-eyebrow">IF YOU SUSPECT FRAUD</span><h2>Act in a clear order.</h2></div></div>
<div class="pv2-process">
<div><p style="font-size:11px;line-height:1.8;color:var(--pv2-muted)">The priority is to stop further exposure, verify account activity through the official banking channel and give support enough context to investigate.</p></div>
<div class="pv2-steps">
<div class="pv2-step"><span>01</span><div><strong>Stop the conversation</strong><small>Do not continue with a caller, message or website you no longer trust.</small></div></div>
<div class="pv2-step"><span>02</span><div><strong>Use the official sign-in route</strong><small>Open Velmora directly and review your recent activity rather than following a message link.</small></div></div>
<div class="pv2-step"><span>03</span><div><strong>Change credentials if exposed</strong><small>If you shared a password, change it immediately through the official banking experience.</small></div></div>
<div class="pv2-step"><span>04</span><div><strong>Contact support</strong><small>Provide the suspicious transaction reference, time and what information may have been disclosed.</small></div></div>
</div>
</div></div></section>

<section class="pv2-section"><div class="pv2-container">
<div class="pv2-section-head"><div><span class="pv2-eyebrow">COMMON ATTACK PATTERNS</span><h2>Recognize the pressure tactics.</h2></div></div>
<div class="pv2-card-grid">
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">call</span></span><h3>Impersonation calls</h3><p>A caller creates urgency and asks for credentials, codes or an immediate “safe account” transfer.</p></article>
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">link</span></span><h3>Fake sign-in links</h3><p>A message directs you to a lookalike banking page designed to capture your credentials.</p></article>
<article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">desktop_windows</span></span><h3>Remote-access scams</h3><p>Someone claiming to help asks you to install software or let them control your device.</p></article>
</div>
</div></section>

<section class="pv2-section pv2-dark"><div class="pv2-container pv2-split">
<div class="pv2-split-copy"><span class="pv2-eyebrow">GOOD SECURITY HABITS</span><h2>Simple habits reduce avoidable risk.</h2><div class="pv2-bullet-list">
<div class="pv2-bullet" style="border-color:rgba(255,255,255,.1)"><span class="material-symbols-rounded">check_circle</span><div><strong>Use a unique banking password</strong><small style="color:#9db0c1">Do not reuse the password from unrelated services.</small></div></div>
<div class="pv2-bullet" style="border-color:rgba(255,255,255,.1)"><span class="material-symbols-rounded">check_circle</span><div><strong>Review transaction activity</strong><small style="color:#9db0c1">Investigate entries you do not recognize instead of waiting for them to resolve themselves.</small></div></div>
<div class="pv2-bullet" style="border-color:rgba(255,255,255,.1)"><span class="material-symbols-rounded">check_circle</span><div><strong>Verify before acting</strong><small style="color:#9db0c1">If a request is unexpected, contact the bank through an official channel.</small></div></div>
</div></div>
<div class="pv2-info-card" style="background:#102d47;border-color:rgba(255,255,255,.1);color:#fff;box-shadow:none"><span class="pv2-eyebrow" style="color:#8fa8bf">SECURITY SUPPORT</span><h2>Concerned about an account?</h2><p style="color:#9db0c1">Contact support using the bank’s published channel rather than contact details supplied in a suspicious message.</p><div class="pv2-actions"><a class="pv2-btn gold" href="/contact-v2/">Contact support</a><a class="pv2-btn secondary" href="<?php echo htmlspecialchars($supportWhatsappLink); ?>" target="_blank" rel="noopener"><?php echo htmlspecialchars($supportPhoneNumber); ?></a></div></div>
</div></section>
</main>
<?php include('../common-sections/public-v2-footer.php'); ?></body></html>