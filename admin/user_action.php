<?php
require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    flash_set('error', 'Please use an account action on the User Management page.');
    redirect('users.php');
}

verify_csrf();

$userId = filter_var(
    $_POST['user_id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$action = is_string($_POST['action'] ?? null) ? $_POST['action'] : '';
$allowedActions = ['disable', 'enable', 'restore'];

if ($userId === false || !in_array($action, $allowedActions, true)) {
    flash_set('error', 'That account action was not valid.');
    redirect('users.php');
}

$pdo = db();
$successMessage = '';

try {
    $pdo->beginTransaction();
    $stmt = $pdo->prepare(
        'SELECT id, public_user_id, username, role, is_disabled, deleted_at
         FROM users
         WHERE id = ?
         LIMIT 1
         FOR UPDATE'
    );
    $stmt->execute([$userId]);
    $target = $stmt->fetch();

    if (!$target || $target['role'] === 'admin' || (int) $target['id'] === (int) $admin['id']) {
        $pdo->rollBack();
        flash_set('error', 'That account cannot be managed here.');
        redirect('users.php');
    }

    $targetPublicId = (string) $target['public_user_id'];
    $isDeleted = !empty($target['deleted_at']);
    $isDisabled = !empty($target['is_disabled']);

    if ($action === 'disable') {
        if ($isDeleted || $isDisabled) {
            $pdo->rollBack();
            flash_set('error', 'This account is already disabled or deleted.');
            redirect('users.php');
        }
        $pdo->prepare('UPDATE users SET is_disabled = 1 WHERE id = ?')->execute([$userId]);
        audit_log($pdo, (int) $admin['id'], 'account_disabled', 'user', $targetPublicId, 'Disabled account ' . $target['username']);
        $successMessage = 'Account disabled. Its records were not changed.';
    } elseif ($action === 'enable') {
        if ($isDeleted || !$isDisabled) {
            $pdo->rollBack();
            flash_set('error', 'Only a disabled, non-deleted account can be enabled.');
            redirect('users.php');
        }
        $pdo->prepare('UPDATE users SET is_disabled = 0 WHERE id = ?')->execute([$userId]);
        audit_log($pdo, (int) $admin['id'], 'account_enabled', 'user', $targetPublicId, 'Enabled account ' . $target['username']);
        $successMessage = 'Account enabled. Its existing account and approval status were preserved.';
    } else {
        if (!$isDeleted) {
            $pdo->rollBack();
            flash_set('error', 'This account is not soft-deleted.');
            redirect('users.php');
        }
        $pdo->prepare('UPDATE users SET is_disabled = 0, deleted_at = NULL WHERE id = ?')->execute([$userId]);
        audit_log($pdo, (int) $admin['id'], 'account_restored', 'user', $targetPublicId, 'Restored account ' . $target['username']);
        $successMessage = 'Account restored. Its existing account and approval status were preserved.';
    }

    $pdo->commit();
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash_set('error', 'The account action could not be saved. No account changes were completed.');
    redirect('users.php');
}

flash_set('success', $successMessage);
redirect('users.php');
