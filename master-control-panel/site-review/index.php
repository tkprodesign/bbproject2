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
  ['Support Center','/support/','Review'],
  ['Security & Fraud Center','/security/','Review'],
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
  ['Sign In','/login/','Review'],
  ['Open an Account','/signup-v2/','Review'],
  ['Onboarding / Next Steps','/onboarding-v2/','Review'],
 ],
 'Customer banking V3'=>[
  ['Overview','/dashboard/','Review'],
  ['Accounts','/dashboard/accounts/','Review'],
  ['Transactions','/dashboard/transactions/','Review'],
  ['Transfer Funds','/dashboard/transfer/','Review'],
  ['Currency Exchange','/dashboard/exchange/','Review'],
  ['Beneficiaries','/dashboard/beneficiaries/','Review'],
  ['Notifications','/dashboard/notifications/','Review'],
  ['Statements','/dashboard/statements/','Review'],
  ['Support Messages','/dashboard/support/','Review'],
  ['Profile','/dashboard/profile/','Review'],
  ['Profile Picture','/dashboard/profile-picture/','Review'],
  ['Identity & KYC','/dashboard/identity/','Review'],
  ['Security','/dashboard/security/','Review'],
  ['Change Password','/dashboard/security/change-password/','Review'],
  ['Preferences','/dashboard/preferences/','Review'],
 ],
 'Bank operations V2'=>[
  ['Operations Overview','/master-control-panel/','Review'],
  ['Customers','/master-control-panel/customers/','Review'],
  ['Accounts Registry','/master-control-panel/accounts/','Review'],
  ['Transactions Registry','/master-control-panel/transactions/','Review'],
  ['Transfer Review','/master-control-panel/transfers/','Review'],
  ['FX Trades','/master-control-panel/fx/','Review'],
  ['Ledger Adjustments','/master-control-panel/adjustments/','Review'],
  ['KYC Review','/master-control-panel/kyc/','Review'],
  ['Support Cases','/master-control-panel/support-cases/','Review'],
  ['Communications','/master-control-panel/communications/','Review'],
  ['Security & Audit','/master-control-panel/audit/','Review'],
  ['Settings','/master-control-panel/settings/','Review'],
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