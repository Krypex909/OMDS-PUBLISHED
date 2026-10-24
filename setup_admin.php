<?php
/**
 * Creates the administrator if none exists.
 * Use this after importing sql/schema.sql by hand.
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

start_omds_session();

try {
    $pdo = db();
} catch (Throwable $e) {
    exit('Database is not ready. Run install.php first or import sql/schema.sql. ' . e($e->getMessage()));
}

$existing = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
if ($existing) {
    echo 'An administrator account already exists. Go to <a href="login.php">login</a>.';
    exit;
}

$username = 'omds_admin';
$gmail = 'omds.admin@gmail.com';
$password = 'AdminOMDS!2026';
$publicId = '1000000001';
$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = $pdo->prepare(
    'INSERT INTO users (public_user_id, username, gmail, password_hash, first_name, last_name, role, account_status)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
);
$stmt->execute([$publicId, $username, $gmail, $hash, 'System', 'Administrator', 'admin', 'active']);
$adminId = (int) $pdo->lastInsertId();
$pdo->prepare('INSERT INTO user_profiles (user_id) VALUES (?)')->execute([$adminId]);
audit_log($pdo, $adminId, 'admin_seeded', 'user', (string) $adminId, 'Administrator created with setup_admin.php');

echo 'Admin created.<br>Username: ' . e($username) . '<br>Account ID: ' . e($publicId) . '<br>Password: ' . e($password) . '<br><a href="login.php">Log in</a>';
