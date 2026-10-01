<?php
require_once __DIR__ . '/../../common-sections/app.php';
velmoraLogoutCustomer(true);
header('Location: /login/');
exit;
