<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (isset($_COOKIE['login_email'])) {
    setcookie('login_email','',time()-3600,'/');
    unset($_COOKIE['login_email']);
}
session_unset();
session_destroy();
header('Location: /login/');
exit;
