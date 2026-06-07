<?php
define('DB_CONFIG', [
    'host'     => getenv('DB_HOST') ?: 'localhost',
    'port'     => (int)(getenv('DB_PORT') ?: 3306),
    'user'     => getenv('DB_USER') ?: 'velmora_user',
    'password' => getenv('DB_PASS') ?: 'VelmoraPass2024!',
    'name'     => getenv('DB_NAME') ?: 'velmora_db',
    'socket'   => getenv('DB_SOCKET') ?: '',
]);
