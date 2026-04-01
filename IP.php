<?php
$ip = $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname());
echo $ip;
