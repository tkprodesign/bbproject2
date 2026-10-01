<?php
declare(strict_types=1);

function velmoraBackendLoadEnvironment(): void {
    static $loaded = false;
    if ($loaded) return;
    $loaded = true;

    $paths = [
        dirname(__DIR__, 2) . '/.velmora-backend.env',
        dirname(__DIR__) . '/.env',
    ];

    foreach ($paths as $path) {
        if (!is_file($path) || !is_readable($path)) continue;
        $lines = @file($path, FILE_IGNORE_NEW_LINES);
        if (!is_array($lines)) continue;
        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '' || str_starts_with($line, '#')) continue;
            if (!preg_match('/^([A-Za-z_][A-Za-z0-9_]*)=(.*)$/s', $line, $m)) continue;
            $key = $m[1];
            $value = trim($m[2]);
            if (strlen($value) >= 2) {
                $first = $value[0];
                $last = $value[strlen($value)-1];
                if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) $value = substr($value, 1, -1);
            }
            if (getenv($key) === false || getenv($key) === '') putenv($key . '=' . $value);
        }
        break;
    }
}
function velmoraBackendFallbackConfig(): array {
    static $fallback = null;
    if (is_array($fallback)) return $fallback;
    $path = __DIR__ . '/backend-fallback.php';
    $fallback = is_file($path) ? (require $path) : [];
    return is_array($fallback) ? $fallback : [];
}

function velmoraBackendRoleConfig(string $role): array {
    velmoraBackendLoadEnvironment();
    $map = [
        'support' => ['email_key' => 'SUPPORT_EMAIL', 'password_key' => 'SUPPORT_EMAIL_PASSWORD', 'route' => '/support-control-panel/', 'label' => 'Support'],
        'admin' => ['email_key' => 'ADMIN_EMAIL', 'password_key' => 'ADMIN_EMAIL_PASSWORD', 'route' => '/control-panel/', 'label' => 'Admin'],
        'master' => ['email_key' => 'DEVELOPER_EMAIL', 'password_key' => 'DEVELOPER_EMAIL_PASSWORD', 'route' => '/master-control-panel/', 'label' => 'Master'],
    ];
    $cfg = $map[$role] ?? [];
    if (!$cfg) return [];

    $fallback = velmoraBackendFallbackConfig()[$role] ?? [];
    $envEmail = strtolower(trim((string)(getenv($cfg['email_key']) ?: '')));
    $cfg['email'] = $envEmail !== '' ? $envEmail : strtolower(trim((string)($fallback['email'] ?? '')));
    $cfg['password'] = (string)(getenv($cfg['password_key']) ?: '');
    $cfg['fallback'] = is_array($fallback) ? $fallback : [];
    return $cfg;
}

function velmoraBackendRoute(string $role): string {
    $cfg = velmoraBackendRoleConfig($role);
    return (string)($cfg['route'] ?? '/backend-login/');
}

function velmoraBackendVerifyPassword(string $input, array $cfg): bool {
    $configured = (string)($cfg['password'] ?? '');
    if ($configured !== '') {
        if (str_starts_with($configured, '$2y
function velmoraAuthenticateBackend(string $email, string $password): ?array {
    $email = strtolower(trim($email));
    foreach (['support', 'admin', 'master'] as $role) {
        $cfg = velmoraBackendRoleConfig($role);
        if (empty($cfg['email'])) continue;
        if (hash_equals((string)$cfg['email'], $email) && velmoraBackendVerifyPassword($password, $cfg)) {
            return ['role' => $role, 'email' => $email, 'label' => $cfg['label'], 'route' => $cfg['route']];
        }
    }
    return null;
}

function velmoraBackendSession(): ?array {
    $auth = $_SESSION['backend_auth'] ?? null;
    if (!is_array($auth) || empty($auth['role']) || empty($auth['email'])) return null;
    $issued = (int)($auth['issued_at'] ?? 0);
    $last = (int)($auth['last_activity'] ?? 0);
    if ($issued <= 0 || $last <= 0 || time() - $issued > 43200 || time() - $last > 7200) {
        unset($_SESSION['backend_auth']);
        return null;
    }
    $_SESSION['backend_auth']['last_activity'] = time();
    return $_SESSION['backend_auth'];
}

function velmoraBackendLoginLocked(): bool {
    $until = (int)($_SESSION['backend_lock_until'] ?? 0);
    if ($until > time()) return true;
    if ($until) unset($_SESSION['backend_lock_until'], $_SESSION['backend_failed_attempts']);
    return false;
}
function velmoraBackendRegisterFailure(): void {
    $count = (int)($_SESSION['backend_failed_attempts'] ?? 0) + 1;
    $_SESSION['backend_failed_attempts'] = $count;
    if ($count >= 5) $_SESSION['backend_lock_until'] = time() + 600;
}

function velmoraBackendEstablish(array $identity): void {
    session_regenerate_id(true);
    $_SESSION['backend_auth'] = [
        'role' => (string)$identity['role'],
        'email' => strtolower((string)$identity['email']),
        'label' => (string)$identity['label'],
        'issued_at' => time(),
        'last_activity' => time(),
    ];
    unset($_SESSION['backend_failed_attempts'], $_SESSION['backend_lock_until']);
}

function velmoraRequireBackendRole(string $role): void {
    $auth = velmoraBackendSession();
    if (!$auth) {
        header('Location: /backend-login/');
        exit;
    }
    if (($auth['role'] ?? '') !== $role) {
        header('Location: ' . velmoraBackendRoute((string)($auth['role'] ?? '')));
        exit;
    }
}

function velmoraBackendLogout(): void {
    unset($_SESSION['backend_auth'], $_SESSION['backend_failed_attempts'], $_SESSION['backend_lock_until']);
    session_regenerate_id(true);
}
) || str_starts_with($configured, '$2a
function velmoraAuthenticateBackend(string $email, string $password): ?array {
    $email = strtolower(trim($email));
    foreach (['support', 'admin', 'master'] as $role) {
        $cfg = velmoraBackendRoleConfig($role);
        if (empty($cfg['email']) || empty($cfg['password'])) continue;
        if (hash_equals((string)$cfg['email'], $email) && velmoraBackendVerifyPassword($password, (string)$cfg['password'])) {
            return ['role' => $role, 'email' => $email, 'label' => $cfg['label'], 'route' => $cfg['route']];
        }
    }
    return null;
}

function velmoraBackendSession(): ?array {
    $auth = $_SESSION['backend_auth'] ?? null;
    if (!is_array($auth) || empty($auth['role']) || empty($auth['email'])) return null;
    $issued = (int)($auth['issued_at'] ?? 0);
    $last = (int)($auth['last_activity'] ?? 0);
    if ($issued <= 0 || $last <= 0 || time() - $issued > 43200 || time() - $last > 7200) {
        unset($_SESSION['backend_auth']);
        return null;
    }
    $_SESSION['backend_auth']['last_activity'] = time();
    return $_SESSION['backend_auth'];
}

function velmoraBackendLoginLocked(): bool {
    $until = (int)($_SESSION['backend_lock_until'] ?? 0);
    if ($until > time()) return true;
    if ($until) unset($_SESSION['backend_lock_until'], $_SESSION['backend_failed_attempts']);
    return false;
}
function velmoraBackendRegisterFailure(): void {
    $count = (int)($_SESSION['backend_failed_attempts'] ?? 0) + 1;
    $_SESSION['backend_failed_attempts'] = $count;
    if ($count >= 5) $_SESSION['backend_lock_until'] = time() + 600;
}

function velmoraBackendEstablish(array $identity): void {
    session_regenerate_id(true);
    $_SESSION['backend_auth'] = [
        'role' => (string)$identity['role'],
        'email' => strtolower((string)$identity['email']),
        'label' => (string)$identity['label'],
        'issued_at' => time(),
        'last_activity' => time(),
    ];
    unset($_SESSION['backend_failed_attempts'], $_SESSION['backend_lock_until']);
}

function velmoraRequireBackendRole(string $role): void {
    $auth = velmoraBackendSession();
    if (!$auth) {
        header('Location: /backend-login/');
        exit;
    }
    if (($auth['role'] ?? '') !== $role) {
        header('Location: ' . velmoraBackendRoute((string)($auth['role'] ?? '')));
        exit;
    }
}

function velmoraBackendLogout(): void {
    unset($_SESSION['backend_auth'], $_SESSION['backend_failed_attempts'], $_SESSION['backend_lock_until']);
    session_regenerate_id(true);
}
) || str_starts_with($configured, '$argon2')) {
            return password_verify($input, $configured);
        }
        return hash_equals($configured, $input);
    }

    $fallback = $cfg['fallback'] ?? [];
    $salt = base64_decode((string)($fallback['salt'] ?? ''), true);
    $expected = base64_decode((string)($fallback['hash'] ?? ''), true);
    $iterations = (int)($fallback['iterations'] ?? 0);
    if ($salt === false || $expected === false || $iterations < 100000) return false;

    $derived = hash_pbkdf2('sha256', $input, $salt, $iterations, 32, true);
    return hash_equals($expected, $derived);
}
function velmoraAuthenticateBackend(string $email, string $password): ?array {
    $email = strtolower(trim($email));
    foreach (['support', 'admin', 'master'] as $role) {
        $cfg = velmoraBackendRoleConfig($role);
        if (empty($cfg['email']) || empty($cfg['password'])) continue;
        if (hash_equals((string)$cfg['email'], $email) && velmoraBackendVerifyPassword($password, (string)$cfg['password'])) {
            return ['role' => $role, 'email' => $email, 'label' => $cfg['label'], 'route' => $cfg['route']];
        }
    }
    return null;
}

function velmoraBackendSession(): ?array {
    $auth = $_SESSION['backend_auth'] ?? null;
    if (!is_array($auth) || empty($auth['role']) || empty($auth['email'])) return null;
    $issued = (int)($auth['issued_at'] ?? 0);
    $last = (int)($auth['last_activity'] ?? 0);
    if ($issued <= 0 || $last <= 0 || time() - $issued > 43200 || time() - $last > 7200) {
        unset($_SESSION['backend_auth']);
        return null;
    }
    $_SESSION['backend_auth']['last_activity'] = time();
    return $_SESSION['backend_auth'];
}

function velmoraBackendLoginLocked(): bool {
    $until = (int)($_SESSION['backend_lock_until'] ?? 0);
    if ($until > time()) return true;
    if ($until) unset($_SESSION['backend_lock_until'], $_SESSION['backend_failed_attempts']);
    return false;
}
function velmoraBackendRegisterFailure(): void {
    $count = (int)($_SESSION['backend_failed_attempts'] ?? 0) + 1;
    $_SESSION['backend_failed_attempts'] = $count;
    if ($count >= 5) $_SESSION['backend_lock_until'] = time() + 600;
}

function velmoraBackendEstablish(array $identity): void {
    session_regenerate_id(true);
    $_SESSION['backend_auth'] = [
        'role' => (string)$identity['role'],
        'email' => strtolower((string)$identity['email']),
        'label' => (string)$identity['label'],
        'issued_at' => time(),
        'last_activity' => time(),
    ];
    unset($_SESSION['backend_failed_attempts'], $_SESSION['backend_lock_until']);
}

function velmoraRequireBackendRole(string $role): void {
    $auth = velmoraBackendSession();
    if (!$auth) {
        header('Location: /backend-login/');
        exit;
    }
    if (($auth['role'] ?? '') !== $role) {
        header('Location: ' . velmoraBackendRoute((string)($auth['role'] ?? '')));
        exit;
    }
}

function velmoraBackendLogout(): void {
    unset($_SESSION['backend_auth'], $_SESSION['backend_failed_attempts'], $_SESSION['backend_lock_until']);
    session_regenerate_id(true);
}
