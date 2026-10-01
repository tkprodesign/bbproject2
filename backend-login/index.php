<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}
require_once __DIR__ . '/../common-sections/backend-auth.php';

$current = velmoraBackendSession();
if ($current) {
    header('Location: ' . velmoraBackendRoute((string)$current['role']));
    exit;
}

if (empty($_SESSION['backend_login_csrf'])) {
    $_SESSION['backend_login_csrf'] = bin2hex(random_bytes(24));
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf = (string)($_POST['csrf'] ?? '');
    if ($csrf === '' || !hash_equals((string)$_SESSION['backend_login_csrf'], $csrf)) {
        $error = 'Unable to sign in. Please try again.';
    } elseif (velmoraBackendLoginLocked()) {
        $error = 'Too many unsuccessful attempts. Please wait before trying again.';
    } else {
        $email = strtolower(trim((string)($_POST['email'] ?? '')));
        $password = (string)($_POST['password'] ?? '');
        $identity = velmoraAuthenticateBackend($email, $password);
        if ($identity) {
            velmoraBackendEstablish($identity);
            header('Location: ' . (string)$identity['route']);
            exit;
        }
        velmoraBackendRegisterFailure();
        $error = 'The email or password was not accepted.';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>Staff Sign In | Velmora Bank</title>
<link rel="icon" type="image/png" href="/assets/images/branding/velmora/icon.png">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@500;600;700;800&display=swap">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,300..600,0..1,-50..200">
<link rel="stylesheet" href="/assets/stylesheets/public-v2.css?v=<?php echo time(); ?>">
</head>
<body>
<div class="pv2-demo">STAFF ACCESS</div>
<main class="pv2-auth-page">
<section class="pv2-auth-side">
<a href="/"><img class="pv2-auth-brand" src="/assets/images/branding/logo.png" alt="Velmora Bank"></a>
<div class="pv2-auth-copy">
<span class="pv2-eyebrow">VELMORA OPERATIONS</span>
<h1>Authorized staff access.</h1>
<p>Support, administration and developer access are separated from customer online banking.</p>
<div class="pv2-auth-points">
<div><span class="material-symbols-rounded">shield_lock</span><div><strong>Role-isolated access</strong><small>Your credentials open only the console assigned to your role.</small></div></div>
<div><span class="material-symbols-rounded">person_off</span><div><strong>Separate from customers</strong><small>Staff access does not depend on the customer banking login cookie.</small></div></div>
</div>
</div>
<div class="pv2-auth-foot">Velmora Bank · Restricted staff environment</div>
</section>
<section class="pv2-auth-main">
<div class="pv2-auth-card">
<a class="pv2-auth-home" href="/"><span class="material-symbols-rounded">arrow_back</span>Back to Velmora</a>
<h2>Staff sign in</h2>
<p>Use the staff email and password assigned to your backend role.</p>
<?php if ($error !== ''): ?><div class="pv2-alert error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
<form method="post" class="pv2-form" autocomplete="off">
<input type="hidden" name="csrf" value="<?php echo htmlspecialchars((string)$_SESSION['backend_login_csrf'], ENT_QUOTES, 'UTF-8'); ?>">
<label><span>Email address</span><input type="email" name="email" autocomplete="username" required></label>
<label><span>Password</span><input type="password" name="password" autocomplete="current-password" required></label>
<button class="pv2-btn primary pv2-auth-submit" type="submit">Sign in to staff console</button>
</form>
<div class="pv2-auth-divider"></div>
<div class="pv2-auth-bottom">Customer? <a href="/login/">Use online banking sign in</a></div>
</div>
</section>
</main>
</body>
</html>
