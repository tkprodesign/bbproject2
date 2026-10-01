<?php
// VELMORA_CLI_MIGRATION_ONLY: production schema migrations are deployment/CLI operations.
// Never expose schema mutation through a public web request.
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

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
        customer_number VARCHAR(32) DEFAULT NULL UNIQUE,
        user_status VARCHAR(32) NOT NULL DEFAULT 'Active',
        restriction_reason TEXT DEFAULT NULL,
        restricted_by VARCHAR(190) DEFAULT NULL,
        restricted_at DATETIME DEFAULT NULL,
        session_version INT UNSIGNED NOT NULL DEFAULT 1,
        last_login_at DATETIME DEFAULT NULL,
        last_login_ip VARCHAR(45) DEFAULT NULL,
        login_count INT UNSIGNED NOT NULL DEFAULT 0,
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
        account_alias VARCHAR(100) DEFAULT NULL,
        opened_at DATETIME DEFAULT NULL,
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
        recipient_name VARCHAR(190) DEFAULT NULL,
        to_account_type VARCHAR(100) DEFAULT NULL,
        to_account_number VARCHAR(50) DEFAULT NULL,
        counter_currency VARCHAR(20) DEFAULT NULL,
        counter_amount DECIMAL(18,2) DEFAULT NULL,
        fx_rate DECIMAL(20,8) DEFAULT NULL,
        fx_spread_bps INT DEFAULT NULL,
        channel VARCHAR(60) NOT NULL DEFAULT 'Online Banking',
        value_date DATE DEFAULT NULL,
        posted_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_transactions_user_email (user_email),
        INDEX idx_transactions_account_number (account_number),
        INDEX idx_transactions_time (time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS fx_trades (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        trade_id VARCHAR(48) NOT NULL UNIQUE,
        user_email VARCHAR(190) NOT NULL,
        from_account_number BIGINT NOT NULL,
        to_account_number BIGINT NOT NULL,
        source_currency VARCHAR(20) NOT NULL,
        target_currency VARCHAR(20) NOT NULL,
        source_amount DECIMAL(18,2) NOT NULL,
        target_amount DECIMAL(18,2) NOT NULL,
        customer_rate DECIMAL(20,8) NOT NULL,
        fx_spread_bps INT NOT NULL DEFAULT 0,
        status VARCHAR(40) NOT NULL DEFAULT 'Quoted',
        quoted_at INT NOT NULL,
        executed_at INT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_fx_trades_user_email (user_email),
        INDEX idx_fx_trades_quoted_at (quoted_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS beneficiaries (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(190) NOT NULL,
        nickname VARCHAR(100) DEFAULT NULL,
        beneficiary_name VARCHAR(190) NOT NULL,
        bank_name VARCHAR(190) NOT NULL,
        account_number VARCHAR(80) NOT NULL,
        account_type VARCHAR(100) DEFAULT NULL,
        currency VARCHAR(20) NOT NULL,
        status VARCHAR(30) NOT NULL DEFAULT 'Active',
        last_used_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_beneficiaries_user_email (user_email),
        UNIQUE KEY uq_beneficiary_account (user_email, bank_name, account_number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS notifications (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(190) NOT NULL,
        title VARCHAR(190) NOT NULL,
        body TEXT NOT NULL,
        notification_type VARCHAR(60) NOT NULL DEFAULT 'General',
        action_url VARCHAR(255) DEFAULT NULL,
        is_read TINYINT(1) NOT NULL DEFAULT 0,
        read_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_notifications_user_email (user_email),
        INDEX idx_notifications_unread (user_email, is_read)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS password_reset_tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(190) NOT NULL,
        token_hash CHAR(64) NOT NULL UNIQUE,
        expires_at DATETIME NOT NULL,
        used_at DATETIME DEFAULT NULL,
        requested_ip VARCHAR(45) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_password_reset_email (user_email),
        INDEX idx_password_reset_expiry (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS customer_remember_tokens (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        selector CHAR(32) NOT NULL UNIQUE,
        token_hash CHAR(64) NOT NULL,
        user_email VARCHAR(190) NOT NULL,
        session_version INT UNSIGNED NOT NULL,
        expires_at DATETIME NOT NULL,
        last_used_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_customer_remember_email (user_email),
        INDEX idx_customer_remember_expiry (expires_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS support_cases (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        case_number VARCHAR(32) NOT NULL UNIQUE,
        user_email VARCHAR(190) NOT NULL,
        category VARCHAR(80) NOT NULL,
        subject VARCHAR(190) NOT NULL,
        status VARCHAR(40) NOT NULL DEFAULT 'Open',
        priority VARCHAR(30) NOT NULL DEFAULT 'Normal',
        related_transaction_id VARCHAR(40) DEFAULT NULL,
        assigned_to VARCHAR(190) DEFAULT NULL,
        last_customer_message_at DATETIME DEFAULT NULL,
        last_operator_message_at DATETIME DEFAULT NULL,
        resolved_at DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_support_cases_user_email (user_email),
        INDEX idx_support_cases_status (status),
        INDEX idx_support_cases_updated_at (updated_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS support_case_messages (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        case_id BIGINT UNSIGNED NOT NULL,
        sender_role VARCHAR(30) NOT NULL,
        sender_email VARCHAR(190) NOT NULL,
        message TEXT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_support_case_messages_case (case_id),
        INDEX idx_support_case_messages_created (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS security_events (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(190) NOT NULL,
        event_type VARCHAR(80) NOT NULL,
        description VARCHAR(255) NOT NULL,
        ip_address VARCHAR(45) DEFAULT NULL,
        user_agent VARCHAR(500) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_security_events_user_email (user_email),
        INDEX idx_security_events_created_at (created_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",

    "CREATE TABLE IF NOT EXISTS user_preferences (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_email VARCHAR(190) NOT NULL UNIQUE,
        timezone VARCHAR(80) NOT NULL DEFAULT 'America/New_York',
        language VARCHAR(12) NOT NULL DEFAULT 'en',
        email_transaction_alerts TINYINT(1) NOT NULL DEFAULT 1,
        email_security_alerts TINYINT(1) NOT NULL DEFAULT 1,
        in_app_notifications TINYINT(1) NOT NULL DEFAULT 1,
        statement_delivery VARCHAR(30) NOT NULL DEFAULT 'Digital',
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
        occupation VARCHAR(120) DEFAULT NULL,
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
        'customer_number' => "`customer_number` VARCHAR(32) DEFAULT NULL AFTER `last_active`",
        'user_status' => "`user_status` VARCHAR(32) NOT NULL DEFAULT 'Active' AFTER `customer_number`",
        'restriction_reason' => "`restriction_reason` TEXT DEFAULT NULL AFTER `user_status`",
        'restricted_by' => "`restricted_by` VARCHAR(190) DEFAULT NULL AFTER `restriction_reason`",
        'restricted_at' => "`restricted_at` DATETIME DEFAULT NULL AFTER `restricted_by`",
        'session_version' => "`session_version` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `restricted_at`",
        'last_login_at' => "`last_login_at` DATETIME DEFAULT NULL AFTER `session_version`",
        'last_login_ip' => "`last_login_ip` VARCHAR(45) DEFAULT NULL AFTER `last_login_at`",
        'login_count' => "`login_count` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `last_login_ip`",
        'created_at' => "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `login_count`",
    ],
    'accounts' => [
        'account_type' => "`account_type` VARCHAR(100) NOT NULL DEFAULT '' AFTER `id`",
        'user_name' => "`user_name` VARCHAR(150) NOT NULL DEFAULT '' AFTER `account_type`",
        'user_email' => "`user_email` VARCHAR(190) NOT NULL DEFAULT '' AFTER `user_name`",
        'currency' => "`currency` VARCHAR(20) NOT NULL DEFAULT 'USD' AFTER `user_email`",
        'account_number' => "`account_number` BIGINT NOT NULL DEFAULT 0 AFTER `currency`",
        'account_status' => "`account_status` VARCHAR(50) NOT NULL DEFAULT 'Active' AFTER `account_number`",
        'creation_time' => "`creation_time` INT NOT NULL DEFAULT 0 AFTER `account_status`",
        'account_alias' => "`account_alias` VARCHAR(100) DEFAULT NULL AFTER `creation_time`",
        'opened_at' => "`opened_at` DATETIME DEFAULT NULL AFTER `account_alias`",
        'created_at' => "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `opened_at`",
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
        'recipient_name' => "`recipient_name` VARCHAR(190) DEFAULT NULL AFTER `to_bank_name`",
        'to_account_type' => "`to_account_type` VARCHAR(100) DEFAULT NULL AFTER `recipient_name`",
        'to_account_number' => "`to_account_number` VARCHAR(50) DEFAULT NULL AFTER `to_account_type`",
        'counter_currency' => "`counter_currency` VARCHAR(20) DEFAULT NULL AFTER `to_account_number`",
        'counter_amount' => "`counter_amount` DECIMAL(18,2) DEFAULT NULL AFTER `counter_currency`",
        'fx_rate' => "`fx_rate` DECIMAL(20,8) DEFAULT NULL AFTER `counter_amount`",
        'fx_spread_bps' => "`fx_spread_bps` INT DEFAULT NULL AFTER `fx_rate`",
        'channel' => "`channel` VARCHAR(60) NOT NULL DEFAULT 'Online Banking' AFTER `fx_spread_bps`",
        'value_date' => "`value_date` DATE DEFAULT NULL AFTER `channel`",
        'posted_at' => "`posted_at` DATETIME DEFAULT NULL AFTER `value_date`",
        'created_at' => "`created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER `posted_at`",
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
        'occupation' => "`occupation` VARCHAR(120) DEFAULT NULL AFTER `source_of_income`",
        'nationality' => "`nationality` VARCHAR(120) NOT NULL DEFAULT '' AFTER `occupation`",
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
    ['users', 'customer_number', 'UNIQUE INDEX `customer_number` (`customer_number`)'],
    ['accounts', 'account_number', 'UNIQUE INDEX `account_number` (`account_number`)'],
    ['accounts', 'idx_accounts_user_email', 'INDEX `idx_accounts_user_email` (`user_email`)'],
    ['accounts', 'idx_accounts_creation_time', 'INDEX `idx_accounts_creation_time` (`creation_time`)'],
    ['transactions', 'transaction_id', 'UNIQUE INDEX `transaction_id` (`transaction_id`)'],
    ['transactions', 'idx_transactions_user_email', 'INDEX `idx_transactions_user_email` (`user_email`)'],
    ['transactions', 'idx_transactions_account_number', 'INDEX `idx_transactions_account_number` (`account_number`)'],
    ['transactions', 'idx_transactions_time', 'INDEX `idx_transactions_time` (`time`)'],
    ['fx_trades', 'trade_id', 'UNIQUE INDEX `trade_id` (`trade_id`)'],
    ['fx_trades', 'idx_fx_trades_user_email', 'INDEX `idx_fx_trades_user_email` (`user_email`)'],
    ['fx_trades', 'idx_fx_trades_quoted_at', 'INDEX `idx_fx_trades_quoted_at` (`quoted_at`)'],
    ['kyc_data', 'idx_kyc_email', 'INDEX `idx_kyc_email` (`email`)'],
    ['kyc_data', 'idx_kyc_status', 'INDEX `idx_kyc_status` (`status`)'],
    ['dynamic_data', 'name', 'UNIQUE INDEX `name` (`name`)'],
];

foreach ($indexMigrations as [$table, $index, $definition]) {
    addIndexIfMissing($db, $table, $index, $definition, $errors);
}

$db->query("UPDATE users SET customer_number = CONCAT('VLM-', LPAD(id, 8, '0')) WHERE customer_number IS NULL OR customer_number = ''");
$db->query("UPDATE users SET user_status = 'Active' WHERE user_status IS NULL OR user_status = ''");
$db->query("UPDATE accounts SET opened_at = FROM_UNIXTIME(creation_time) WHERE opened_at IS NULL AND creation_time > 0");
$db->query("UPDATE transactions SET channel = 'Online Banking' WHERE channel IS NULL OR channel = ''");
$db->query("UPDATE transactions SET value_date = DATE(FROM_UNIXTIME(time)) WHERE value_date IS NULL AND time > 0");
$db->query("UPDATE transactions SET posted_at = FROM_UNIXTIME(time) WHERE posted_at IS NULL AND time > 0");
$db->query("INSERT IGNORE INTO user_preferences (user_email) SELECT email FROM users WHERE email <> ''");

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
    echo "Tables managed: users, accounts, transactions, fx_trades, beneficiaries, notifications, password_reset_tokens, customer_remember_tokens, support_cases, support_case_messages, security_events, user_preferences, kyc_data, dynamic_data.\n";
} else {
    echo "Finished with errors:\n- " . implode("\n- ", $errors) . "\n";
}

$db->close();
