<?php

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/../config/db.php';

function current_user(): ?array
{
    start_omds_session();
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    static $cached = null;
    static $cachedId = null;
    if ($cached !== null && $cachedId === (int) $_SESSION['user_id']) {
        return $cached;
    }

    $stmt = db()->prepare(
        'SELECT u.*, pp.review_status, pp.organization_name, pp.professional_role
         FROM users u
         LEFT JOIN personnel_profiles pp ON pp.user_id = u.id
         WHERE u.id = ?
         LIMIT 1'
    );
    $stmt->execute([(int) $_SESSION['user_id']]);
    $cached = $stmt->fetch() ?: null;
    $cachedId = (int) $_SESSION['user_id'];
    return $cached;
}

function require_login_ajax(): array
{
    $user = current_user();
    if (
        !$user
        || ($user['account_status'] ?? '') === 'inactive'
        || !empty($user['is_disabled'])
        || !empty($user['deleted_at'])
    ) {
        http_response_code(401);
        exit('Please log in again.');
    }
    return $user;
}

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash_set('error', 'Please log in to continue.');
        redirect(app_base_url() . '/login.php');
    }
    if (
        ($user['account_status'] ?? '') === 'inactive'
        || !empty($user['is_disabled'])
        || !empty($user['deleted_at'])
    ) {
        $_SESSION = [];
        session_destroy();
        start_omds_session();
        flash_set('error', 'This account is not active.');
        redirect(app_base_url() . '/login.php');
    }
    return $user;
}

function require_admin(): array
{
    $user = require_login();
    if (($user['role'] ?? '') !== 'admin') {
        http_response_code(403);
        exit('Only administrators can open this page.');
    }
    return $user;
}

function require_approved_personnel(): array
{
    $user = require_login();
    if (!is_approved_personnel($user) || ($user['review_status'] ?? '') !== 'approved') {
        flash_set('error', 'Authorized medical access is not available for this account.');
        redirect(app_base_url() . '/mainNormalUser.php');
    }
    return $user;
}

function require_organization(): array
{
    $user = require_login();
    if (($user['role'] ?? '') !== 'organization') {
        http_response_code(403);
        exit('This page is for organization accounts.');
    }
    if (($user['account_status'] ?? '') !== 'active') {
        flash_set('error', 'This organization account is not active yet.');
        redirect(app_base_url() . '/login.php');
    }
    return $user;
}

function login_user(array $user): void
{
    start_omds_session();
    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['role'] = $user['role'];
}

function logout_user(): void
{
    start_omds_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'] ?? false, $params['httponly'] ?? true);
    }
    session_destroy();
}

function home_path_for(array $user): string
{
    $base = app_base_url();
    $role = $user['role'] ?? '';
    $status = $user['account_status'] ?? '';

    if ($role === 'admin') {
        return $base . '/admin/dashboard.php';
    }
    if ($role === 'organization') {
        if ($status !== 'active') {
            return $base . '/login.php';
        }
        return $base . '/org/dashboard.php';
    }
    if ($role === 'personnel' && $status === 'active') {
        return $base . '/mainAuthPersonnel.php';
    }
    return $base . '/mainNormalUser.php';
}

function personnel_can_access_records(array $user): bool
{
    return is_approved_personnel($user) && ($user['review_status'] ?? '') === 'approved';
}
