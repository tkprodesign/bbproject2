<?php
/**
 * Database bootstrapper for Velmora Bank.
 * Creates required tables if they do not already exist.
 */

require_once __DIR__ . '/common-sections/app.php';

$db = connectToDatabase();
if (!$db) {
    die('Database connection failed.');
}

$queries = [
    "CREATE TABLE IF NOT EXISTS users (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        email VARCHAR(190) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        date_registered INT NOT NULL,
        human_time VARCHAR(100) NOT NULL,
        kyc_level TINYINT UNSIGNED NOT NULL DEFAULT 1,
        profile_picture VARCHAR(255) DEFAULT NULL,
        last_active INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS accounts (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        account_type VARCHAR(100) NOT NULL,
        user_name VARCHAR(150) NOT NULL,
        user_email VARCHAR(190) NOT NULL,
        currency VARCHAR(20) NOT NULL,
        account_number BIGINT NOT NULL UNIQUE,
        account_status VARCHAR(50) NOT NULL DEFAULT 'Active',
        creation_time INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_accounts_user_email (user_email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS transactions (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        type VARCHAR(80) NOT NULL,
        transaction_id VARCHAR(40) NOT NULL UNIQUE,
        user_email VARCHAR(190) NOT NULL,
        account_number BIGINT NOT NULL,
        amount DECIMAL(18,2) NOT NULL,
        currency VARCHAR(20) NOT NULL,
        description TEXT,
        status VARCHAR(40) NOT NULL DEFAULT 'Pending',
        time INT NOT NULL,
        to_bank_name VARCHAR(190) DEFAULT NULL,
        to_account_type VARCHAR(100) DEFAULT NULL,
        to_account_number VARCHAR(50) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_transactions_user_email (user_email),
        INDEX idx_transactions_account_number (account_number),
        INDEX idx_transactions_time (time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS kyc_data (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(120) NOT NULL,
        middle_name VARCHAR(120) DEFAULT NULL,
        last_name VARCHAR(120) NOT NULL,
        suffix VARCHAR(50) DEFAULT NULL,
        gender VARCHAR(30) DEFAULT NULL,
        address1 VARCHAR(255) NOT NULL,
        address2 VARCHAR(255) DEFAULT NULL,
        apartment_no VARCHAR(80) DEFAULT NULL,
        city VARCHAR(120) NOT NULL,
        state VARCHAR(120) NOT NULL,
        phone_number VARCHAR(40) NOT NULL,
        date_of_birth VARCHAR(30) NOT NULL,
        zip_code VARCHAR(30) NOT NULL,
        us_citizen VARCHAR(30) DEFAULT NULL,
        dual_citizenship VARCHAR(100) DEFAULT NULL,
        country_of_residence VARCHAR(120) NOT NULL,
        source_of_income VARCHAR(120) NOT NULL,
        nationality VARCHAR(120) NOT NULL,
        email VARCHAR(190) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'Pending',
        description TEXT,
        time_uploaded DATETIME NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_kyc_email (email),
        INDEX idx_kyc_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS dynamic_data (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        value TEXT DEFAULT NULL,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
];

$errors = [];
foreach ($queries as $query) {
    if (!$db->query($query)) {
        $errors[] = $db->error;
    }
}

$seedData = [
    ['phone_number', '+17252885411'],
    ['btc_address', ''],
    ['eth_address', ''],
    ['usdt_address', ''],
    ['doge_address', ''],
];

$seedStmt = $db->prepare('INSERT IGNORE INTO dynamic_data (`name`, `value`) VALUES (?, ?)');
if ($seedStmt) {
    foreach ($seedData as [$name, $value]) {
        $seedStmt->bind_param('ss', $name, $value);
        $seedStmt->execute();
    }
    $seedStmt->close();
}

header('Content-Type: text/plain');
if (empty($errors)) {
    echo "Success: database tables are ready.\n";
} else {
    echo "Completed with errors:\n- " . implode("\n- ", $errors) . "\n";
}

$db->close();
