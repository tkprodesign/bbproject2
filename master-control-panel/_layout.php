<?php
function cpv2Nav(string $href,string $icon,string $label,string $active,string $key): string {
    $class=$active===$key?' class="active"':'';
    return '<a'.$class.' href="'.$href.'"><span class="material-symbols-rounded">'.$icon.'</span><span>'.$label.'</span></a>';
}
function cpv2Start(string $title,string $active): void { ?>
<!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"><meta name="robots" content="noindex,nofollow">
<title><?php echo htmlspecialchars($title); ?> | Velmora Operations</title>
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/control-panel-v2.css?v=<?php echo time(); ?>"></head><body>
<div class="op-demo">DEMO OPERATIONS</div>
<div class="op-shell">
<aside class="op-sidebar" id="opSidebar">
 <a class="op-brand" href="/"><img src="/assets/images/branding/velmora/logo.png" alt="Velmora Bank"></a>
 <div class="op-product"><span>VELMORA</span><strong>Operations Console</strong></div>
 <nav class="op-nav">
  <p>OVERVIEW</p><?php echo cpv2Nav('/master-control-panel/','dashboard','Overview',$active,'overview'); ?><?php echo cpv2Nav('/master-control-panel/site-review/','fact_check','Site Review',$active,'review'); ?>
  <p>CUSTOMERS</p><?php echo cpv2Nav('/master-control-panel/customers/','group','Customers',$active,'customers'); ?><?php echo cpv2Nav('/master-control-panel/accounts/','account_balance','Accounts',$active,'accounts'); ?><?php echo cpv2Nav('/master-control-panel/kyc/','badge','KYC Review',$active,'kyc'); ?>
  <p>MONEY MOVEMENT</p><?php echo cpv2Nav('/master-control-panel/transactions/','receipt_long','Transactions',$active,'transactions'); ?><?php echo cpv2Nav('/master-control-panel/transfers/','payments','Transfer Review',$active,'transfers'); ?><?php echo cpv2Nav('/master-control-panel/fx/','currency_exchange','FX Trades',$active,'fx'); ?><?php echo cpv2Nav('/master-control-panel/adjustments/','balance','Ledger Adjustments',$active,'adjustments'); ?>
  <p>OPERATIONS</p><?php echo cpv2Nav('/master-control-panel/mail/','mail','Mail',$active,'mail'); ?><?php echo cpv2Nav('/master-control-panel/support-cases/','support_agent','Support Cases',$active,'support'); ?><?php echo cpv2Nav('/master-control-panel/communications/','campaign','Communications',$active,'communications'); ?><?php echo cpv2Nav('/master-control-panel/audit/','shield','Security & Audit',$active,'audit'); ?><?php echo cpv2Nav('/master-control-panel/settings/','settings','Settings',$active,'settings'); ?>
 </nav>
 <div class="op-bottom"><a href="/dashboard/"><span class="material-symbols-rounded">account_circle</span>Customer dashboard</a><a href="/master-control-panel/logout/"><span class="material-symbols-rounded">logout</span>Sign out</a></div>
</aside>
<div class="op-overlay" id="opOverlay"></div>
<main class="op-main">
<header class="op-top"><div><button id="opMenu" type="button"><span class="material-symbols-rounded">menu</span></button><div><span>BANK OPERATIONS</span><strong><?php echo htmlspecialchars($title); ?></strong></div></div><div class="op-admin"><span class="material-symbols-rounded">admin_panel_settings</span><div><strong>Authorized operator</strong><small><?php echo htmlspecialchars($_SESSION['user_email']??''); ?></small></div></div></header>
<div class="op-page">
<?php $success=cpv2TakeFlash('success');$error=cpv2TakeFlash('error');if($success): ?><div class="op-alert success"><?php echo htmlspecialchars($success); ?></div><?php endif;if($error): ?><div class="op-alert error"><?php echo htmlspecialchars($error); ?></div><?php endif;
}
function cpv2End(): void { ?></div></main></div><script src="/assets/scripts/control-panel-v2.js?v=<?php echo time(); ?>"></script></body></html><?php }
