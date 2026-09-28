<?php
/**
 * Michael Griffin demo-account seed.
 *
 * This file creates simulated account/transaction data for a consented demo profile.
 * It is never used for real customer records. The dashboard shows a visible DEMO label.
 */

function seedMichaelDemoAccountData(string $email, string $name): bool {
    $configuredEmail = strtolower(trim((string) getenv('MICHAEL_EMAIL')));
    $emailMatchesSecret = $configuredEmail !== '' && strcasecmp($email, $configuredEmail) === 0;
    $nameMatchesDemo = strcasecmp(trim($name), 'Michael Griffin') === 0;

    if (!$emailMatchesSecret && !$nameMatchesDemo) {
        return false;
    }

    $db = connectToDatabase();

    // Do not overwrite an established account that happens to share the same display name.
    $accountCount = 0;
    $countStmt = $db->prepare('SELECT COUNT(*) FROM accounts WHERE user_email = ?');
    if ($countStmt) {
        $countStmt->bind_param('s', $email);
        $countStmt->execute();
        $countStmt->bind_result($accountCount);
        $countStmt->fetch();
        $countStmt->close();
    }

    $sentinelId = 'DEMO-MGR-20251229-PENDING';
    $sentinelCount = 0;
    $sentinelStmt = $db->prepare('SELECT COUNT(*) FROM transactions WHERE user_email = ? AND transaction_id = ?');
    if ($sentinelStmt) {
        $sentinelStmt->bind_param('ss', $email, $sentinelId);
        $sentinelStmt->execute();
        $sentinelStmt->bind_result($sentinelCount);
        $sentinelStmt->fetch();
        $sentinelStmt->close();
    }

    // Existing non-demo data is left untouched.
    if ((int) $accountCount > 0 && (int) $sentinelCount === 0) {
        $db->close();
        return false;
    }

    $profilePicture = 'michael-griffin.png';
    $canonicalName = 'Michael Griffin';
    $profileStmt = $db->prepare('UPDATE users SET name = ?, profile_picture = ? WHERE email = ?');
    if ($profileStmt) {
        $profileStmt->bind_param('sss', $canonicalName, $profilePicture, $email);
        $profileStmt->execute();
        $profileStmt->close();
    }

    $accountNumber = null;
    if ((int) $accountCount > 0) {
        $accountStmt = $db->prepare('SELECT account_number FROM accounts WHERE user_email = ? ORDER BY id DESC LIMIT 1');
        if ($accountStmt) {
            $accountStmt->bind_param('s', $email);
            $accountStmt->execute();
            $accountStmt->bind_result($accountNumber);
            $accountStmt->fetch();
            $accountStmt->close();
        }
    }

    if (empty($accountNumber)) {
        $accountNumber = 200009741;
        while (true) {
            $numberCount = 0;
            $numberStmt = $db->prepare('SELECT COUNT(*) FROM accounts WHERE account_number = ?');
            $numberStmt->bind_param('i', $accountNumber);
            $numberStmt->execute();
            $numberStmt->bind_result($numberCount);
            $numberStmt->fetch();
            $numberStmt->close();

            if ((int) $numberCount === 0) {
                break;
            }
            $accountNumber++;
        }

        $accountType = 'Premier Checking';
        $currency = 'USD';
        $accountStatus = 'Active';
        $createdAt = strtotime('2024-02-01 09:00:00');

        $createAccount = $db->prepare(
            'INSERT INTO accounts (account_type, user_name, user_email, currency, account_number, account_status, creation_time) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        if ($createAccount) {
            $createAccount->bind_param(
                'ssssisi',
                $accountType,
                $canonicalName,
                $email,
                $currency,
                $accountNumber,
                $accountStatus,
                $createdAt
            );
            $createAccount->execute();
            $createAccount->close();
        }
    }

    if ((int) $sentinelCount === 0) {
        // Nearly two years of light, doctor-related demo activity.
        // The signed amounts total exactly $980,000.00, including the $50,000 pending item.
        $transactions = [
            ['DEMO-MGR-20240212-1', 'Deposit',  169700.00, 'Medical Practice Revenue', 'Successful', '2024-02-12 09:25:00'],
            ['DEMO-MGR-20240405-1', 'Deposit',  142000.00, 'Insurance Reimbursement', 'Successful', '2024-04-05 11:40:00'],
            ['DEMO-MGR-20240618-1', 'Deposit',   96000.00, 'Hospital Consulting Payment', 'Successful', '2024-06-18 15:10:00'],
            ['DEMO-MGR-20240830-1', 'Deposit',  168000.00, 'Medical Practice Revenue', 'Successful', '2024-08-30 10:05:00'],
            ['DEMO-MGR-20241011-1', 'Deposit',  131500.00, 'Insurance Reimbursement', 'Successful', '2024-10-11 13:20:00'],
            ['DEMO-MGR-20241220-1', 'Deposit',  112000.00, 'Specialist Consultation Fees', 'Successful', '2024-12-20 16:00:00'],
            ['DEMO-MGR-20250207-1', 'Deposit',  155000.00, 'Medical Partnership Distribution', 'Successful', '2025-02-07 10:30:00'],
            ['DEMO-MGR-20250425-1', 'Deposit',  128000.00, 'Medical Practice Revenue', 'Successful', '2025-04-25 12:15:00'],
            ['DEMO-MGR-20250603-1', 'Payment',  -12500.00, 'Property Maintenance', 'Successful', '2025-06-03 08:45:00'],
            ['DEMO-MGR-20250714-1', 'Payment',   -4500.00, 'Professional Association Dues', 'Successful', '2025-07-14 09:10:00'],
            ['DEMO-MGR-20250809-1', 'Payment',   -9000.00, 'Medical Equipment Service', 'Successful', '2025-08-09 14:35:00'],
            ['DEMO-MGR-20250917-1', 'Payment',   -3200.00, 'Home Utilities', 'Successful', '2025-09-17 18:05:00'],
            ['DEMO-MGR-20251006-1', 'Payment',   -5800.00, 'Vehicle Service', 'Successful', '2025-10-06 11:25:00'],
            ['DEMO-MGR-20251112-1', 'Payment',  -11700.00, 'Professional Liability Insurance', 'Successful', '2025-11-12 10:55:00'],
            ['DEMO-MGR-20251218-1', 'Payment',  -18000.00, 'Customs Duty - Medical Equipment Import', 'Successful', '2025-12-18 12:40:00'],
            ['DEMO-MGR-20251222-1', 'Payment',   -7500.00, 'Delivery Fee - Medical Equipment Shipment', 'Successful', '2025-12-22 15:15:00'],
            ['DEMO-MGR-20251229-PENDING', 'Transfer', -50000.00, 'Medical Equipment Supplier Payment', 'Pending', '2025-12-29 10:20:00'],
        ];

        $currency = 'USD';
        $insert = $db->prepare(
            'INSERT IGNORE INTO transactions (type, transaction_id, user_email, account_number, amount, currency, description, status, time) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        if ($insert) {
            foreach ($transactions as [$transactionId, $type, $amount, $description, $status, $date]) {
                $timestamp = strtotime($date);
                $insert->bind_param(
                    'sssidsssi',
                    $type,
                    $transactionId,
                    $email,
                    $accountNumber,
                    $amount,
                    $currency,
                    $description,
                    $status,
                    $timestamp
                );
                $insert->execute();
            }
            $insert->close();
        }
    }

    $db->close();
    return true;
}
?>