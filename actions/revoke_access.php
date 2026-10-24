<?php
require_once __DIR__ . '/../includes/access.php';

$user = require_login();
verify_csrf();
$id = (int) ($_POST['access_id'] ?? 0);

$stmt = db()->prepare('SELECT * FROM record_access WHERE id = ? AND owner_user_id = ? LIMIT 1');
$stmt->execute([$id, (int) $user['id']]);
$row = $stmt->fetch();
if (!$row) {
    flash_set('error', 'Access request not found.');
    redirect('../mainNormalUser.php?page=6');
}

db()->prepare('UPDATE record_access SET status = ?, revoked_at = NOW() WHERE id = ?')->execute(['revoked', $id]);
audit_log(db(), (int) $user['id'], 'access_revoked', 'record_access', (string) $row['grantee_user_id'], 'Owner revoked access');
flash_set('success', 'Access revoked.');
redirect('../mainNormalUser.php?page=6');
