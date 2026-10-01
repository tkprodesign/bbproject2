<?php
function v3NavItem(string $href, string $icon, string $label, string $active, string $key, int $badge = 0): string {
    $class = $active === $key ? ' class="active"' : '';
    $badgeHtml = $badge > 0 ? '<b class="v3-nav-badge">' . ($badge > 99 ? '99+' : $badge) . '</b>' : '';
    return '<a' . $class . ' href="' . htmlspecialchars($href) . '"><span class="material-symbols-rounded">' . $icon . '</span><span>' . htmlspecialchars($label) . '</span>' . $badgeHtml . '</a>';
}

function v3PageStart(string $title, string $active, array $profile, ?string $profilePicture = null): void {
    global $user_email;
    $first = explode(' ', trim((string)$profile['name']))[0] ?? 'Client';
    $unreadNotifications = function_exists('v3UnreadNotificationCount') ? v3UnreadNotificationCount((string)$user_email) : 0;
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title><?php echo htmlspecialchars($title); ?> | Velmora Bank</title>
    <link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
    <link rel="stylesheet" href="/assets/stylesheets/dashboard-v3.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
</head>
<body>
<div class="v3-demo">DEMO ENVIRONMENT</div>
<div class="v3-shell">
    <aside class="v3-sidebar" id="v3Sidebar">
        <a href="/" class="v3-brand"><img src="/assets/images/branding/velmora/logo.png" alt="Velmora Bank"></a>
        <nav class="v3-nav">
            <p>OVERVIEW</p>
            <?php echo v3NavItem('/dashboard-v3/','grid_view','Overview',$active,'overview'); ?>
            <?php echo v3NavItem('/dashboard-v3/transactions/','receipt_long','Transactions',$active,'transactions'); ?>
            <?php echo v3NavItem('/dashboard-v3/notifications/','notifications','Notifications',$active,'notifications',$unreadNotifications); ?>
            <p>BANKING</p>
            <?php echo v3NavItem('/dashboard-v3/transfer/','north_east','Transfer funds',$active,'transfer'); ?>
            <?php echo v3NavItem('/dashboard-v3/exchange/','currency_exchange','Currency exchange',$active,'exchange'); ?>
            <?php echo v3NavItem('/dashboard-v3/accounts/','account_balance_wallet','Accounts',$active,'accounts'); ?>
            <?php echo v3NavItem('/dashboard-v3/beneficiaries/','group','Beneficiaries',$active,'beneficiaries'); ?>
            <p>PROFILE</p>
            <?php echo v3NavItem('/dashboard-v3/profile/','person','Profile',$active,'profile'); ?>
            <?php echo v3NavItem('/dashboard-v3/security/','shield_lock','Security',$active,'security'); ?>
        </nav>
        <div class="v3-side-bottom">
            <a href="/contact/"><span class="material-symbols-rounded">support_agent</span><span>Support</span></a>
            <a href="?logout=1"><span class="material-symbols-rounded">logout</span><span>Sign out</span></a>
        </div>
    </aside>
    <div class="v3-overlay" id="v3Overlay"></div>
    <main class="v3-main">
        <header class="v3-topbar">
            <div class="v3-top-left">
                <button type="button" id="v3Menu" class="v3-menu"><span class="material-symbols-rounded">menu</span></button>
                <div><span class="v3-top-label">PRIVATE BANKING</span><strong><?php echo htmlspecialchars($title); ?></strong></div>
            </div>
            <div class="v3-user">
                <div><strong><?php echo htmlspecialchars($profile['name']); ?></strong><span><?php echo htmlspecialchars($profile['occupation']); ?></span></div>
                <?php if ($profilePicture && $profilePicture !== 'nil'): ?>
                    <img src="/dashboard/security/complete-kyc/uploads/<?php echo htmlspecialchars($profilePicture); ?>" alt="Profile">
                <?php else: ?>
                    <span class="v3-avatar"><?php echo htmlspecialchars(strtoupper(substr($first,0,1))); ?></span>
                <?php endif; ?>
            </div>
        </header>
        <div class="v3-page">
    <?php
}

function v3PageEnd(): void {
    ?>
        </div>
    </main>
</div>
<script src="/assets/scripts/dashboard-v3.js?v=<?php echo time(); ?>"></script>
</body>
</html>
    <?php
}

function v3FlashMessages(): void {
    $success = v3Flash('success');
    $error = v3Flash('error');
    if ($success) echo '<div class="v3-alert success"><span class="material-symbols-rounded">check_circle</span><div>' . htmlspecialchars($success) . '</div></div>';
    if ($error) echo '<div class="v3-alert error"><span class="material-symbols-rounded">error</span><div>' . htmlspecialchars($error) . '</div></div>';
}
