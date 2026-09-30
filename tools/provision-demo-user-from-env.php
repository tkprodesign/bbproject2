<?php
/**
 * One-time demo profile provisioner.
 * Reads CRAIG_FISHER_* values from a private .env file on the server.
 * Intended only for the Velmora simulated/demo environment.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$envFile = $argv[1] ?? '';
if ($envFile === '' || !is_file($envFile)) {
    fwrite(STDERR, "Private env file not found.\n");
    exit(1);
}

$vars = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
        continue;
    }
    [$key, $value] = explode('=', $line, 2);
    $key = trim($key);
    $value = trim($value);
    if (
        (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
        (str_starts_with($value, "'") && str_ends_with($value, "'"))
    ) {
        $value = substr($value, 1, -1);
    }
    $vars[$key] = $value;
}

$required = [
    'CRAIG_FISHER_EMAIL',
    'CRAIG_FISHER_PASSWORD',
    'CRAIG_FISHER_NAME',
    'CRAIG_FISHER_DOB',
    'CRAIG_FISHER_ADDRESS',
    'CRAIG_FISHER_PHONE',
    'CRAIG_FISHER_CITY',
    'CRAIG_FISHER_COUNTRY',
];

foreach ($required as $key) {
    if (trim((string)($vars[$key] ?? '')) === '') {
        fwrite(STDERR, "Missing required env value: {$key}\n");
        exit(1);
    }
}

require_once __DIR__ . '/../common-sections/app.php';

$email = strtolower(trim($vars['CRAIG_FISHER_EMAIL']));
$password = (string)$vars['CRAIG_FISHER_PASSWORD'];
$name = trim($vars['CRAIG_FISHER_NAME']);
$dob = trim($vars['CRAIG_FISHER_DOB']);
$address = trim($vars['CRAIG_FISHER_ADDRESS']);
$phone = trim($vars['CRAIG_FISHER_PHONE']);
$city = trim($vars['CRAIG_FISHER_CITY']);
$country = trim($vars['CRAIG_FISHER_COUNTRY']);

$firstName = strtok($name, ' ') ?: $name;
$lastName = trim(substr($name, strlen($firstName)));
if ($lastName === '') {
    $lastName = $firstName;
}

$state = 'AL';
$zip = '35223';
$sourceOfIncome = 'Not provided';
$nationality = 'Not provided';
$kycStatus = 'Demo';
$kycDescription = 'DEMO ACCOUNT — Simulated profile for presentation and testing only.';

$db = connectToDatabase();
$db->begin_transaction();

try {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $now = time();
    $humanTime = date('H:i | d/m/Y', $now) . ' | New York Time';

    $userId = null;
    $stmt = $db->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($userId);
    $userExists = $stmt->fetch();
    $stmt->close();

    if ($userExists) {
        $stmt = $db->prepare('UPDATE users SET name = ?, password = ?, kyc_level = 1 WHERE email = ?');
        $stmt->bind_param('sss', $name, $hash, $email);
    } else {
        $stmt = $db->prepare('INSERT INTO users (name, email, password, date_registered, human_time, kyc_level) VALUES (?, ?, ?, ?, ?, 1)');
        $stmt->bind_param('sssis', $name, $email, $hash, $now, $humanTime);
    }
    $stmt->execute();
    $stmt->close();

    $accountCount = 0;
    $stmt = $db->prepare('SELECT COUNT(*) FROM accounts WHERE user_email = ?');
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($accountCount);
    $stmt->fetch();
    $stmt->close();

    if ((int)$accountCount === 0) {
        do {
            $accountNumber = random_int(2000000000, 2999999999);
            $taken = 0;
            $stmt = $db->prepare('SELECT COUNT(*) FROM accounts WHERE account_number = ?');
            $stmt->bind_param('i', $accountNumber);
            $stmt->execute();
            $stmt->bind_result($taken);
            $stmt->fetch();
            $stmt->close();
        } while ((int)$taken > 0);

        $accountType = 'Personal Checking';
        $currency = 'USD';
        $accountStatus = 'Active';

        $stmt = $db->prepare(
            'INSERT INTO accounts (account_type, user_name, user_email, currency, account_number, account_status, creation_time) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->bind_param(
            'ssssisi',
            $accountType,
            $name,
            $email,
            $currency,
            $accountNumber,
            $accountStatus,
            $now
        );
        $stmt->execute();
        $stmt->close();
    }

    $demoKycId = null;
    $stmt = $db->prepare("SELECT id FROM kyc_data WHERE email = ? AND LOWER(status) = 'demo' ORDER BY id DESC LIMIT 1");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($demoKycId);
    $hasDemoKyc = $stmt->fetch();
    $stmt->close();

    $uploaded = date('Y-m-d H:i:s');

    if ($hasDemoKyc) {
        $stmt = $db->prepare(
            'UPDATE kyc_data SET first_name=?, last_name=?, address1=?, city=?, state=?, phone_number=?, date_of_birth=?, zip_code=?, country_of_residence=?, source_of_income=?, nationality=?, status=?, description=?, time_uploaded=? WHERE id=?'
        );
        $stmt->bind_param(
            'ssssssssssssssi',
            $firstName,
            $lastName,
            $address,
            $city,
            $state,
            $phone,
            $dob,
            $zip,
            $country,
            $sourceOfIncome,
            $nationality,
            $kycStatus,
            $kycDescription,
            $uploaded,
            $demoKycId
        );
    } else {
        $stmt = $db->prepare(
            'INSERT INTO kyc_data (first_name,last_name,address1,city,state,phone_number,date_of_birth,zip_code,country_of_residence,source_of_income,nationality,email,status,description,time_uploaded) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
        );
        $stmt->bind_param(
            'sssssssssssssss',
            $firstName,
            $lastName,
            $address,
            $city,
            $state,
            $phone,
            $dob,
            $zip,
            $country,
            $sourceOfIncome,
            $nationality,
            $email,
            $kycStatus,
            $kycDescription,
            $uploaded
        );
    }
    $stmt->execute();
    $stmt->close();

    $db->commit();

    $result = $db->query("SELECT COUNT(*) AS c FROM users");
    $countRow = $result ? $result->fetch_assoc() : ['c' => '?'];

    echo "CRAIG_DEMO_ACCOUNT_READY=YES\n";
    echo "REGISTERED_USERS=" . ($countRow['c'] ?? '?') . "\n";
} catch (Throwable $e) {
    $db->rollback();
    fwrite(STDERR, "Provisioning failed: " . $e->getMessage() . "\n");
    exit(1);
} finally {
    $db->close();
}
