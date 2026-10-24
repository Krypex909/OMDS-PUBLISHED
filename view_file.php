<?php
require_once __DIR__ . '/includes/access.php';

$user = require_login();
$id = (int) ($_GET['id'] ?? 0);
if ($id < 1) {
    http_response_code(400);
    exit('File not found.');
}

$stmt = db()->prepare('SELECT * FROM medical_files WHERE id = ? LIMIT 1');
$stmt->execute([$id]);
$file = $stmt->fetch();
if (!$file) {
    http_response_code(404);
    exit('File not found.');
}

$ownerId = (int) $file['owner_user_id'];
if (!personnel_may_view_owner($user, $ownerId)) {
    audit_log(db(), (int) $user['id'], 'file_view_denied', 'medical_file', (string) $id, 'Missing permission');
    http_response_code(403);
    exit('You do not have permission to view this file.');
}

$path = __DIR__ . '/' . $file['stored_path'];
if (!is_file($path)) {
    http_response_code(404);
    exit('File is missing on the server.');
}

audit_log(db(), (int) $user['id'], 'file_viewed', 'medical_record', (string) $ownerId, 'Viewed file ' . $file['original_name']);

$ext = strtolower(pathinfo($file['original_name'], PATHINFO_EXTENSION));
$types = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
];
header('Content-Type: ' . ($types[$ext] ?? 'application/octet-stream'));
header('Content-Disposition: inline; filename="' . basename($file['original_name']) . '"');
header('X-Content-Type-Options: nosniff');
readfile($path);
exit;
