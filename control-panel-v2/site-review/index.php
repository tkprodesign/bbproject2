<?php
require_once __DIR__ . '/../_app.php';
require_once __DIR__ . '/../_layout.php';

$groups=[
 'Public website'=>[
  ['Home','/','Active / approved'],
  ['Personal Banking','/personal-v2/','Review'],
  ['Business Banking','/business-v2/','Review'],
  ['Credit Cards','/credit-card-v2/','Review'],
  ['Loans & Financing','/loan-v2/','Review'],
  ['International Banking & FX','/international-v2/','Review'],
  ['Online Banking','/online-banking-v2/','Review'],
  ['About Velmora','/about-v2/','Review'],
  ['Support Center','/support-v2/','Review'],
  ['Security & Fraud Center','/security-v2/','Review'],
  ['Locations','/locations-v2/','Review'],
  ['Contact','/contact-v2/','Review'],
  ['Careers','/careers-v2/','Review'],
  ['Rates & Fees','/rates-v2/','Review'],
  ['Documents & Forms','/documents-v2/','Review'],
 ],
 'Legal & trust'=>[
  ['Legal & Disclosures','/legal-v2/','Review'],
  ['Privacy Policy','/privacy-v2/','Review'],
  ['Terms of Use','/terms-v2/','Review'],
  ['Cookie Policy','/cookie-policy-v2/','Review'],
  ['Accessibility','/accessibility-v2/','Review'],
 ],
 'Authentication & onboarding'=>[
  ['Sign In','/login-v2/','Review'],
  ['Open an Account','/signup-v2/','Review'],
  ['Onboarding / Next Steps','/onboarding-v2/','Review'],
 ],
 'Customer banking V3'=>[
  ['Overview','/dashboard-v3/','Review'],
  ['Accounts','/dashboard-v3/accounts/','Review'],
  ['Transactions','/dashboard-v3/transactions/','Review'],
  ['Transfer Funds','/dashboard-v3/transfer/','Review'],
  ['Currency Exchange','/dashboard-v3/exchange/','Review'],
  ['Beneficiaries','/dashboard-v3/beneficiaries/','Review'],
  ['Notifications','/dashboard-v3/notifications/','Review'],
  ['Statements','/dashboard-v3/statements/','Review'],
  ['Support Messages','/dashboard-v3/support/','Review'],
  ['Profile','/dashboard-v3/profile/','Review'],
  ['Profile Picture','/dashboard-v3/profile-picture/','Review'],
  ['Identity & KYC','/dashboard-v3/identity/','Review'],
  ['Security','/dashboard-v3/security/','Review'],
  ['Change Password','/dashboard-v3/security/change-password/','Review'],
  ['Preferences','/dashboard-v3/preferences/','Review'],
 ],
 'Bank operations V2'=>[
  ['Operations Overview','/control-panel-v2/','Review'],
  ['Customers','/control-panel-v2/customers/','Review'],
  ['Accounts Registry','/control-panel-v2/accounts/','Review'],
  ['Transactions Registry','/control-panel-v2/transactions/','Review'],
  ['Transfer Review','/control-panel-v2/transfers/','Review'],
  ['FX Trades','/control-panel-v2/fx/','Review'],
  ['Ledger Adjustments','/control-panel-v2/adjustments/','Review'],
  ['KYC Review','/control-panel-v2/kyc/','Review'],
  ['Support Cases','/control-panel-v2/support-cases/','Review'],
  ['Communications','/control-panel-v2/communications/','Review'],
  ['Security & Audit','/control-panel-v2/audit/','Review'],
  ['Settings','/control-panel-v2/settings/','Review'],
 ],
];

cpv2Start('Site Review Hub','review');
?>
<section class="op-heading">
 <div><span class="op-kicker">SITE OVERHAUL</span><h1>Review hub</h1><p>One place to review every professional replacement before you approve promotion to the live route. Current predecessors remain untouched until approval.</p></div>
</section>

<div class="op-alert success">Archive rule: when a replacement is approved, the predecessor must be preserved with its PHP/HTML, page-specific CSS, JavaScript, assets and any dependency notes needed to reproduce the old behavior.</div>

<?php foreach($groups as $group=>$items): ?>
<section class="op-panel" style="margin-bottom:18px">
 <div class="op-panel-head"><div><span class="op-kicker">REVIEW GROUP</span><h2><?php echo htmlspecialchars($group); ?></h2></div><span><?php echo count($items); ?> pages</span></div>
 <div class="op-review-grid">
 <?php foreach($items as [$name,$url,$status]): ?>
  <a class="op-review-card" href="<?php echo htmlspecialchars($url); ?>" target="_blank" rel="noopener">
    <div><strong><?php echo htmlspecialchars($name); ?></strong><small><?php echo htmlspecialchars($url); ?></small></div>
    <span class="op-status <?php echo $status==='Active / approved'?'approved':'pending'; ?>"><?php echo htmlspecialchars($status); ?></span>
    <span class="material-symbols-rounded">open_in_new</span>
  </a>
 <?php endforeach; ?>
 </div>
</section>
<?php endforeach; ?>

<section class="op-panel">
 <div class="op-panel-head"><div><span class="op-kicker">RETIRE AFTER APPROVAL</span><h2>Routes that should not remain separate products</h2></div></div>
 <div class="op-list">
  <article><span class="material-symbols-rounded">merge</span><div><strong>/sign-up/</strong><small>Consolidate into the approved /signup/ experience.</small></div></article>
  <article><span class="material-symbols-rounded">folder_delete</span><div><strong>/quick-links/</strong><small>Distribute its useful content into Support, Security, Legal and Documents.</small></div></article>
  <article><span class="material-symbols-rounded">history</span><div><strong>Legacy dashboard versions</strong><small>Archive completely once V3 is approved and promoted to /dashboard/.</small></div></article>
 </div>
</section>
<?php cpv2End(); ?>