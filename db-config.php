<?php
define('DB_CONFIG', [
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'port'     => (int)(getenv('DB_PORT') ?: 3306),
    'user'     => getenv('DB_USER') ?: 'rjhzxfeknu_user',
    'password' => getenv('DB_PASS') ?: 'Wateva06@',
    'name'     => getenv('DB_NAME') ?: 'rjhzxfeknu_db',
    'socket'   => getenv('DB_SOCKET') ?: '',
]);
