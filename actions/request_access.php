<?php
require_once __DIR__ . '/../includes/access.php';

$user = require_login();
verify_csrf();

if (!personnel_can_access_records($user)) {
    flash_set('error', 'Only approved authorized personnel can request record access.');
    redirect('../mainNormalUser.php');
}

$ownerId = (int) ($_POST['owner_id'] ?? 0);
$purpose = clean_text($_POST['purpose'] ?? '', 255);
if ($ownerId < 1 || $ownerId === (int) $user['id'] || $purpose === '') {
    flash_set('error', 'Invalid access request.');
    redirect('../scan.php');
}

$owner = load_user_with_profile($ownerId);
if (!$owner || !in_array($owner['role'], ['user', 'personnel'], true)) {
    flash_set('error', 'That account cannot share medical records this way.');
    redirect('../scan.php');
}

$existing = current_access_row(db(), $ownerId, (int) $user['id']);
if ($existing && in_array($existing['status'], ['pending', 'granted'], true)) {
    flash_set('error', 'An access request for this user already exists.');
    redirect('../scan.php?account_id=' . urlencode($owner['public_user_id']));
}

db()->prepare(
    'INSERT INTO record_access (owner_user_id, grantee_user_id, purpose, status, is_emergency)
     VALUES (?, ?, ?, ?, 0)'
)->execute([$ownerId, (int) $user['id'], $purpose, 'pending']);
audit_log(db(), (int) $user['id'], 'access_requested', 'record_access', (string) $ownerId, $purpose);
flash_set('success', 'Access requested. The user must grant it before records are shown.');
redirect('../scan.php?account_id=' . urlencode($owner['public_user_id']));
