<?php require_once('../common-sections/app.php'); ?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0">
<meta name="robots" content="noindex,nofollow"><title>Loans & Financing | Velmora Bank</title><meta name="description" content="Structured lending information and an illustrative loan estimator for Velmora clients.">
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>"></head><body>
<?php include('../common-sections/public-v2-header.php'); ?>
<main>
<section class="pv2-page-hero"><div class="pv2-container pv2-hero-grid">
  <div><span class="pv2-eyebrow">LOANS &amp; FINANCING</span><h1>Borrowing should start with clarity.</h1>
  <p>Understand the structure before you apply. Velmora’s lending journey is designed around a clear estimate, an application review and a final decision—not vague promises.</p>
  <div class="pv2-actions"><a class="pv2-btn primary" href="#estimator">Estimate borrowing</a><a class="pv2-btn secondary" href="/contact/">Speak to lending support</a></div></div>
  <div class="pv2-hero-image"><img src="/assets/images/home/features/mortgage-and-loans.jpg" alt="Loans and financing"><div class="pv2-image-note"><small>LENDING</small><strong>Estimate → Apply → Review → Decision</strong></div></div>
</div></section>

<section class="pv2-trust"><div class="pv2-container pv2-trust-grid">
  <div><span class="material-symbols-rounded">calculate</span><p><strong>Illustrative estimate</strong><small>Understand the structure before submitting.</small></p></div>
  <div><span class="material-symbols-rounded">fact_check</span><p><strong>Application review</strong><small>Loan requests are assessed before approval.</small></p></div>
  <div><span class="material-symbols-rounded">description</span><p><strong>Clear terms</strong><small>Final terms should be confirmed before acceptance.</small></p></div>
  <div><span class="material-symbols-rounded">support_agent</span><p><strong>Human support</strong><small>Ask questions before you commit.</small></p></div>
</div></section>

<section class="pv2-section" id="estimator"><div class="pv2-container">
  <div class="pv2-section-head"><div><span class="pv2-eyebrow">ILLUSTRATIVE ESTIMATOR</span><h2>See the current demo lending calculation before you continue.</h2></div><p>This estimator reflects the site’s current lending calculation and is not an approval or binding credit offer.</p></div>
  <div class="pv2-calculator">
    <div class="pv2-calc-form"><form class="pv2-form" onsubmit="return false">
      <label><span>Monthly salary (USD)</span><input id="loanSalary" type="number" min="1" step="0.01" placeholder="e.g. 5000"></label>
      <label><span>Tenor</span><select id="loanTenor"><option value="">Choose tenor</option><option value="14">14 days</option><option value="30">30 days</option><option value="60">60 days</option></select></label>
      <button id="loanCalculate" class="pv2-btn primary" type="button">Calculate illustration</button>
    </form><p class="pv2-disclaimer">The current demo logic uses up to 40% of monthly salary, capped at $12,000, with the existing tenor-based fee calculation.</p></div>
    <div class="pv2-calc-result"><span class="pv2-eyebrow">ESTIMATE</span><div class="pv2-estimate" id="loanEstimate"><span>Enter a monthly salary and tenor to view an illustration.</span></div><a id="loanApplyLink" class="pv2-btn primary disabled" href="#">Continue to application</a><p class="pv2-disclaimer">Continuing opens the current application workflow with the illustrated values pre-filled for review.</p></div>
  </div>
</div></section>

<section class="pv2-section pv2-soft"><div class="pv2-container">
  <div class="pv2-section-head"><div><span class="pv2-eyebrow">HOW THE JOURNEY SHOULD WORK</span><h2>No mystery between estimate and decision.</h2></div></div>
  <div class="pv2-card-grid">
    <article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">calculate</span></span><h3>1. Estimate</h3><p>Review an illustrative amount, fee and repayment structure before providing application details.</p></article>
    <article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">edit_document</span></span><h3>2. Apply</h3><p>Submit identity, contact and loan-purpose information for assessment.</p></article>
    <article class="pv2-card"><span class="pv2-card-icon"><span class="material-symbols-rounded">rule</span></span><h3>3. Review</h3><p>The bank assesses the request and communicates the outcome and any final terms.</p></article>
  </div>
</div></section>

<section class="pv2-section"><div class="pv2-container pv2-split">
  <div class="pv2-split-copy"><span class="pv2-eyebrow">RESPONSIBLE BORROWING</span><h2>The application should never be the first time you understand the cost.</h2><p>Professional lending pages separate estimates from approvals, explain what is illustrative, and reserve final pricing and eligibility for the actual assessment process.</p>
    <div class="pv2-bullet-list">
      <div class="pv2-bullet"><span class="material-symbols-rounded">check_circle</span><div><strong>Estimate is not approval</strong><small>The calculator helps frame a request; it does not guarantee credit.</small></div></div>
      <div class="pv2-bullet"><span class="material-symbols-rounded">check_circle</span><div><strong>Final terms require review</strong><small>Approved amount, fees and repayment terms should be confirmed explicitly.</small></div></div>
      <div class="pv2-bullet"><span class="material-symbols-rounded">check_circle</span><div><strong>Questions before commitment</strong><small>Support should remain available before a borrower accepts final terms.</small></div></div>
    </div>
  </div>
  <div class="pv2-split-image"><img src="/assets/images/home/hero/consulting-banner.jpg" alt="Financial consultation"></div>
</div></section>

<section class="pv2-cta"><div class="pv2-container"><div><span class="pv2-eyebrow" style="color:#8fa8bf">LENDING SUPPORT</span><h2>Need help understanding the next step?</h2></div><div><a class="pv2-btn gold" href="/contact/">Talk to lending support</a><a class="pv2-btn secondary" href="#estimator">Use estimator</a></div></div></section>
</main>
<?php include('../common-sections/public-v2-footer.php'); ?></body></html>