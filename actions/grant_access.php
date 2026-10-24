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

$expires = date('Y-m-d H:i:s', time() + 8 * 3600);
db()->prepare(
    'UPDATE record_access SET status = ?, granted_at = NOW(), revoked_at = NULL, expires_at = ? WHERE id = ?'
)->execute(['granted', $expires, $id]);
audit_log(db(), (int) $user['id'], 'access_granted', 'record_access', (string) $row['grantee_user_id'], 'Owner granted access');
flash_set('success', 'Access granted for 8 hours. You can revoke it at any time.');
redirect('../mainNormalUser.php?page=6');
