<?php
/**
 * Database bootstrapper for Velmora Bank.
 * Creates and updates required tables if they do not already exist.
 */

require_once __DIR__ . '/common-sections/app.php';

$db = connectToDatabase();
if (!$db) {
    die('Database connection failed.');
}

function columnExists(mysqli $db, string $table, string $column): bool {
    $stmt = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $table, $column);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return (int)$count > 0;
}

function addColumnIfMissing(mysqli $db, string $table, string $column, string $definition, array &$errors): void {
    if (columnExists($db, $table, $column)) {
        return;
    }

    if (!$db->query("ALTER TABLE `$table` ADD COLUMN $definition")) {
        $errors[] = "Unable to add `$table`.`$column`: " . $db->error;
    }
}

function indexExists(mysqli $db, string $table, string $index): bool {
    $stmt = $db->prepare('SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?');
    if (!$stmt) {
        return false;
    }

    $stmt->bind_param('ss', $table, $index);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    return (int)$count > 0;
}

function addIndexIfMissing(mysqli $db, string $table, string $index, string $definition, array &$errors): void {
    if (indexExists($db, $table, $index)) {
        return;
    }

    if (!$db->query("ALTER TABLE `$table` ADD $definition")) {
        $errors[] = "Unable to add `$table` index `$index`: " . $db->error;
    }
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
        INDEX idx_accounts_user_email (user_email),
        INDEX idx_accounts_creation_time (creation_time)
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

$columnMigrations = [
    'users' => [
        'name' => "`name` VARCHAR(150) NOT NULL DEFAULT '' AFTER `id`",
        'email' => "`email` VARCHAR(190) NOT NULL DEFAULT '' AFTER `name`",
        'password' => "`password` VARCHAR(255) NOT NULL DEFAULT '' AFTER `email`",
        'date_registered' => "`date_registered` INT NOT NULL DEFAULT 0 AFTER `password`",
        'human_time' => "`human_time` VARCHAR(100) NOT NULL DEFAULT '' AFTER `date_registered`",
        'kyc_level' => "`kyc_level` TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER `human_time`",
        'profile_picture' => "`profile_picture` VARCHAR(255) DEFAULT NULL AFTER `kyc_level`",
        'last_active' => "`last_active` INT DEFAULT NULL AFTER `profile_picture`",
        'created_at' => "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `last_active`",
    ],
    'accounts' => [
        'account_type' => "`account_type` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id`",
        'user_name' => "`user_name` VARCHAR(150) NOT NULL DEFAULT '' AFTER `account_type`",
        'user_email' => "`user_email` VARCHAR(190) NOT NULL DEFAULT '' AFTER `user_name`",
        'currency' => "`currency` VARCHAR(20) NOT NULL DEFAULT 'USD' AFTER `user_email`",
        'account_number' => "`account_number` BIGINT NOT NULL DEFAULT 0 AFTER `currency`",
        'account_status' => "`account_status` VARCHAR(50) NOT NULL DEFAULT 'Active' AFTER `account_number`",
        'creation_time' => "`creation_time` INT NOT NULL DEFAULT 0 AFTER `account_status`",
        'created_at' => "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `creation_time`",
    ],
    'transactions' => [
        'type' => "`type` VARCHAR(80) NOT NULL DEFAULT '' AFTER `id`",
        'transaction_id' => "`transaction_id` VARCHAR(40) NOT NULL DEFAULT '' AFTER `type`",
        'user_email' => "`user_email` VARCHAR(190) NOT NULL DEFAULT '' AFTER `transaction_id`",
        'account_number' => "`account_number` BIGINT NOT NULL DEFAULT 0 AFTER `user_email`",
        'amount' => "`amount` DECIMAL(18,2) NOT NULL DEFAULT 0.00 AFTER `account_number`",
        'currency' => "`currency` VARCHAR(20) NOT NULL DEFAULT 'USD' AFTER `amount`",
        'description' => "`description` TEXT AFTER `currency`",
        'status' => "`status` VARCHAR(40) NOT NULL DEFAULT 'Pending' AFTER `description`",
        'time' => "`time` INT NOT NULL DEFAULT 0 AFTER `status`",
        'to_bank_name' => "`to_bank_name` VARCHAR(190) DEFAULT NULL AFTER `time`",
        'to_account_type' => "`to_account_type` VARCHAR(100) DEFAULT NULL AFTER `to_bank_name`",
        'to_account_number' => "`to_account_number` VARCHAR(50) DEFAULT NULL AFTER `to_account_type`",
        'created_at' => "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `to_account_number`",
    ],
    'kyc_data' => [
        'first_name' => "`first_name` VARCHAR(120) NOT NULL DEFAULT '' AFTER `id`",
        'middle_name' => "`middle_name` VARCHAR(120) DEFAULT NULL AFTER `first_name`",
        'last_name' => "`last_name` VARCHAR(120) NOT NULL DEFAULT '' AFTER `middle_name`",
        'suffix' => "`suffix` VARCHAR(50) DEFAULT NULL AFTER `last_name`",
        'gender' => "`gender` VARCHAR(30) DEFAULT NULL AFTER `suffix`",
        'address1' => "`address1` VARCHAR(255) NOT NULL DEFAULT '' AFTER `gender`",
        'address2' => "`address2` VARCHAR(255) DEFAULT NULL AFTER `address1`",
        'apartment_no' => "`apartment_no` VARCHAR(80) DEFAULT NULL AFTER `address2`",
        'city' => "`city` VARCHAR(120) NOT NULL DEFAULT '' AFTER `apartment_no`",
        'state' => "`state` VARCHAR(120) NOT NULL DEFAULT '' AFTER `city`",
        'phone_number' => "`phone_number` VARCHAR(40) NOT NULL DEFAULT '' AFTER `state`",
        'date_of_birth' => "`date_of_birth` VARCHAR(30) NOT NULL DEFAULT '' AFTER `phone_number`",
        'zip_code' => "`zip_code` VARCHAR(30) NOT NULL DEFAULT '' AFTER `date_of_birth`",
        'us_citizen' => "`us_citizen` VARCHAR(30) DEFAULT NULL AFTER `zip_code`",
        'dual_citizenship' => "`dual_citizenship` VARCHAR(100) DEFAULT NULL AFTER `us_citizen`",
        'country_of_residence' => "`country_of_residence` VARCHAR(120) NOT NULL DEFAULT '' AFTER `dual_citizenship`",
        'source_of_income' => "`source_of_income` VARCHAR(120) NOT NULL DEFAULT '' AFTER `country_of_residence`",
        'nationality' => "`nationality` VARCHAR(120) NOT NULL DEFAULT '' AFTER `source_of_income`",
        'email' => "`email` VARCHAR(190) NOT NULL DEFAULT '' AFTER `nationality`",
        'status' => "`status` VARCHAR(30) NOT NULL DEFAULT 'Pending' AFTER `email`",
        'description' => "`description` TEXT AFTER `status`",
        'time_uploaded' => "`time_uploaded` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `description`",
        'created_at' => "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `time_uploaded`",
    ],
    'dynamic_data' => [
        'name' => "`name` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id`",
        'value' => "`value` TEXT DEFAULT NULL AFTER `name`",
        'updated_at' => "`updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `value`",
    ],
];

foreach ($columnMigrations as $table => $columns) {
    foreach ($columns as $column => $definition) {
        addColumnIfMissing($db, $table, $column, $definition, $errors);
    }
}

$indexMigrations = [
    ['users', 'email', 'UNIQUE INDEX `email` (`email`)'],
    ['accounts', 'account_number', 'UNIQUE INDEX `account_number` (`account_number`)'],
    ['accounts', 'idx_accounts_user_email', 'INDEX `idx_accounts_user_email` (`user_email`)'],
    ['accounts', 'idx_accounts_creation_time', 'INDEX `idx_accounts_creation_time` (`creation_time`)'],
    ['transactions', 'transaction_id', 'UNIQUE INDEX `transaction_id` (`transaction_id`)'],
    ['transactions', 'idx_transactions_user_email', 'INDEX `idx_transactions_user_email` (`user_email`)'],
    ['transactions', 'idx_transactions_account_number', 'INDEX `idx_transactions_account_number` (`account_number`)'],
    ['transactions', 'idx_transactions_time', 'INDEX `idx_transactions_time` (`time`)'],
    ['kyc_data', 'idx_kyc_email', 'INDEX `idx_kyc_email` (`email`)'],
    ['kyc_data', 'idx_kyc_status', 'INDEX `idx_kyc_status` (`status`)'],
    ['dynamic_data', 'name', 'UNIQUE INDEX `name` (`name`)'],
];

foreach ($indexMigrations as [$table, $index, $definition]) {
    addIndexIfMissing($db, $table, $index, $definition, $errors);
}

$seedStmt = $db->prepare('INSERT IGNORE INTO dynamic_data (`name`, `value`) VALUES (?, ?)');
if ($seedStmt) {
    foreach (getDefaultDynamicData() as $name => $value) {
        $seedStmt->bind_param('ss', $name, $value);
        $seedStmt->execute();
    }
    $seedStmt->close();
}

header('Content-Type: text/plain');
if (empty($errors)) {
    echo "Success: database tables are ready.\n";
    echo "Tables managed: users, accounts, transactions, kyc_data, dynamic_data.\n";
} else {
    echo "Finished with errors:\n- " . implode("\n- ", $errors) . "\n";
}

$db->close();
