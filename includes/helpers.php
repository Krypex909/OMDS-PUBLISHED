<?php

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function start_omds_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

function csrf_token(): string
{
    start_omds_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(400);
        exit('The form could not be verified. Please go back and try again.');
    }
}

function flash_set(string $type, string $message): void
{
    start_omds_session();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    start_omds_session();
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function redirect(string $path): void
{
    header('Location: ' . $path);
    exit;
}

function app_base_url(): string
{
    $https = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
    $scheme = $https ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $script = $_SERVER['SCRIPT_NAME'] ?? '/';
    $dir = str_replace('\\', '/', dirname($script));
    if (preg_match('#/(admin|org|pages)$#', $dir)) {
        $dir = dirname($dir);
    }
    if ($dir === '/' || $dir === '\\' || $dir === '.') {
        $dir = '';
    }
    return $scheme . '://' . $host . $dir;
}

function generate_public_user_id(PDO $pdo): string
{
    do {
        $id = str_pad((string) random_int(0, 9999999999), 10, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare('SELECT id FROM users WHERE public_user_id = ? LIMIT 1');
        $stmt->execute([$id]);
        $exists = $stmt->fetch();
    } while ($exists);

    return $id;
}

function generate_qr_token(PDO $pdo): string
{
    do {
        $token = bin2hex(random_bytes(32));
        $stmt = $pdo->prepare('SELECT id FROM qr_tokens WHERE token = ? LIMIT 1');
        $stmt->execute([$token]);
        $exists = $stmt->fetch();
    } while ($exists);

    return $token;
}

function is_gmail(string $email): bool
{
    return (bool) preg_match('/^[A-Z0-9._%+\-]+@gmail\.com$/i', $email);
}

function clean_text(string $value, int $max = 255): string
{
    $value = trim($value);
    if (function_exists('mb_substr')) {
        return mb_substr($value, 0, $max);
    }
    return substr($value, 0, $max);
}

function audit_log(PDO $pdo, ?int $actorId, string $action, string $targetType = '', string $targetId = '', string $details = ''): void
{
    $stmt = $pdo->prepare(
        'INSERT INTO audit_logs (actor_user_id, action, target_type, target_id, details)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([$actorId, $action, $targetType, $targetId, $details]);
}

function display_name(array $user): string
{
    $parts = array_filter([
        $user['first_name'] ?? '',
        $user['middle_name'] ?? '',
        $user['last_name'] ?? '',
        $user['suffix'] ?? '',
    ]);
    $name = trim(implode(' ', $parts));
    return $name !== '' ? $name : ($user['username'] ?? 'Account');
}

function is_approved_personnel(array $user): bool
{
    return ($user['role'] ?? '') === 'personnel'
        && ($user['account_status'] ?? '') === 'active';
}

function can_use_user_shell(array $user): bool
{
    $role = $user['role'] ?? '';
    $status = $user['account_status'] ?? '';
    if ($role === 'user') {
        return $status === 'active';
    }
    if ($role === 'personnel') {
        return in_array($status, ['active', 'pending_personnel', 'denied_personnel'], true);
    }
    return false;
}
