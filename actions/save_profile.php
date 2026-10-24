<?php
require_once __DIR__ . '/../includes/access.php';

$user = require_login();
if (!can_use_user_shell($user)) {
    redirect(home_path_for($user));
}
verify_csrf();

$pdo = db();
$first = clean_text($_POST['first_name'] ?? '', 80);
$middle = clean_text($_POST['middle_name'] ?? '', 80);
$last = clean_text($_POST['last_name'] ?? '', 80);
$suffix = clean_text($_POST['suffix'] ?? '', 20);
$blood = clean_text($_POST['blood_type'] ?? '', 16);
$address = clean_text($_POST['address'] ?? '', 255);
$dob = clean_text($_POST['date_of_birth'] ?? '', 10);
$sex = clean_text($_POST['sex'] ?? '', 20);
$religion = clean_text($_POST['religion'] ?? '', 80);
$contact = clean_text($_POST['contact_number'] ?? '', 40);
$emergency = clean_text($_POST['emergency_contact'] ?? '', 120);
$emergencyPhone = clean_text($_POST['emergency_phone'] ?? '', 40);
$conditions = clean_text($_POST['medical_conditions'] ?? '', 2000);
$allergies = clean_text($_POST['allergies'] ?? '', 2000);
$meds = clean_text($_POST['current_medications'] ?? '', 2000);
$history = clean_text($_POST['medical_history'] ?? '', 2000);
$status = clean_text($_POST['general_status'] ?? '', 2000);

if ($first === '' || $last === '') {
    flash_set('error', 'First and last name are required.');
    redirect('../mainNormalUser.php?page=1');
}

$picturePath = null;
if (!empty($_FILES['profile_picture']['name']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg', 'jpeg', 'png'], true) && $_FILES['profile_picture']['size'] <= 3 * 1024 * 1024) {
        $folder = __DIR__ . '/../uploads/profiles';
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }
        $picturePath = 'uploads/profiles/' . (int) $user['id'] . '.' . $ext;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], __DIR__ . '/../' . $picturePath);
    }
}

$pdo->prepare(
    'UPDATE users SET first_name = ?, middle_name = ?, last_name = ?, suffix = ?, address = ?, blood_type = ? WHERE id = ?'
)->execute([$first, $middle, $last, $suffix, $address, $blood, $user['id']]);

$dobValue = preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob) ? $dob : null;

$sql = 'UPDATE user_profiles SET date_of_birth = ?, sex = ?, religion = ?, contact_number = ?, emergency_contact = ?, emergency_phone = ?, medical_conditions = ?, allergies = ?, current_medications = ?, medical_history = ?, general_status = ?';
$params = [$dobValue, $sex, $religion, $contact, $emergency, $emergencyPhone, $conditions, $allergies, $meds, $history, $status];
if ($picturePath) {
    $sql .= ', profile_picture = ?';
    $params[] = $picturePath;
}
$sql .= ' WHERE user_id = ?';
$params[] = $user['id'];
$pdo->prepare($sql)->execute($params);

audit_log($pdo, (int) $user['id'], 'profile_updated', 'user', (string) $user['id'], 'User updated profile');
flash_set('success', 'Profile saved.');
redirect('../mainNormalUser.php?page=1');
