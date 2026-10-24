<?php
require_once __DIR__ . '/../includes/access.php';

$user = require_login();
if (!can_use_user_shell($user)) {
    redirect(home_path_for($user));
}
verify_csrf();

$category = clean_text($_POST['category'] ?? '', 120);
$file = $_FILES['medical_file'] ?? null;
if ($category === '' || !$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
    flash_set('error', 'Please choose a category and a file.');
    redirect('../mainNormalUser.php?page=2');
}

$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true) || $file['size'] > 8 * 1024 * 1024) {
    flash_set('error', 'File must be PDF, JPG, or PNG and 8MB or smaller.');
    redirect('../mainNormalUser.php?page=2');
}

$folder = __DIR__ . '/../uploads/medical/' . (int) $user['id'];
if (!is_dir($folder)) {
    mkdir($folder, 0755, true);
}
$stored = 'uploads/medical/' . (int) $user['id'] . '/' . bin2hex(random_bytes(8)) . '.' . $ext;
if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/../' . $stored)) {
    flash_set('error', 'The file could not be saved.');
    redirect('../mainNormalUser.php?page=2');
}

db()->prepare(
    'INSERT INTO medical_files (owner_user_id, category, original_name, stored_path) VALUES (?, ?, ?, ?)'
)->execute([(int) $user['id'], $category, clean_text($file['name'], 255), $stored]);
audit_log(db(), (int) $user['id'], 'file_uploaded', 'medical_record', (string) $user['id'], $category);
flash_set('success', 'File uploaded.');
redirect('../mainNormalUser.php?page=2');
