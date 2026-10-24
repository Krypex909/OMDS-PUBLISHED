<?php
/**
 * Admin-only viewer for a personnel application ID document.
 * This is separate from view_file.php, which serves medical records.
 */
require_once __DIR__ . '/../includes/auth.php';

$admin = require_admin();

$applicationId = (int) ($_GET['id'] ?? 0);
if ($applicationId < 1) {
    http_response_code(400);
    exit('Application not found.');
}

$stmt = db()->prepare(
    'SELECT id, company_id_file, review_status
     FROM personnel_profiles
     WHERE id = ?
     LIMIT 1'
);
$stmt->execute([$applicationId]);
$row = $stmt->fetch();

if (!$row || trim((string) $row['company_id_file']) === '') {
    http_response_code(404);
    exit('No professional ID file is stored for this application.');
}

$relativePath = str_replace('\\', '/', (string) $row['company_id_file']);
$allowedPrefix = 'uploads/personnel_ids/';

if (
    str_contains($relativePath, '..')
    || !str_starts_with($relativePath, $allowedPrefix)
) {
    http_response_code(400);
    exit('Invalid ID file path.');
}

$idsDir = realpath(__DIR__ . '/../uploads/personnel_ids');
$fullPath = realpath(__DIR__ . '/../' . $relativePath);

if ($idsDir === false || $fullPath === false || !is_file($fullPath)) {
    http_response_code(404);
    exit('ID file is missing on the server.');
}

$idsDirNormalized = strtolower(str_replace('\\', '/', $idsDir));
$fullPathNormalized = strtolower(str_replace('\\', '/', $fullPath));
if (!str_starts_with($fullPathNormalized, $idsDirNormalized . '/')) {
    http_response_code(400);
    exit('Invalid ID file path.');
}

$ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
$types = [
    'pdf' => 'application/pdf',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
];

if (!isset($types[$ext])) {
    http_response_code(400);
    exit('Unsupported ID file type.');
}

audit_log(
    db(),
    (int) $admin['id'],
    'personnel_id_viewed',
    'personnel_profile',
    (string) $applicationId,
    'Admin viewed professional ID for application #' . $applicationId
);

$downloadName = 'professional-id-application-' . $applicationId . '.' . $ext;
header('Content-Type: ' . $types[$ext]);
header('Content-Disposition: inline; filename="' . $downloadName . '"');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
readfile($fullPath);
exit;
