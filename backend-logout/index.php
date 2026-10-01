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
velmoraBackendLogout();
header('Location: /backend-login/?signed_out=1');
exit;
