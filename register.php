<?php
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/auth.php';

start_omds_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('SignUp.php');
}

verify_csrf();

$accountType = $_POST['accountType'] ?? '';
$isPersonnel = $accountType === 'Authorized Personnel Account Registration';
$isUser = $accountType === 'User Account Registration';

$firstName = clean_text($_POST['firstName'] ?? '', 80);
$middleName = clean_text($_POST['middleName'] ?? '', 80);
$lastName = clean_text($_POST['lastName'] ?? '', 80);
$suffix = clean_text($_POST['suffix'] ?? '', 20);
$username = clean_text($_POST['username'] ?? '', 50);
$address = clean_text($_POST['address'] ?? '', 255);
$gmail = strtolower(clean_text($_POST['email'] ?? '', 255));
$password = $_POST['password'] ?? '';
$bloodType = clean_text($_POST['bloodType'] ?? '', 16);
$terms = isset($_POST['terms']);

$companyName = clean_text($_POST['companyName'] ?? '', 160);
$companyId = clean_text($_POST['companyId'] ?? '', 80);
$companyAddress = clean_text($_POST['companyAddress'] ?? '', 255);
$companyContact = clean_text($_POST['companyContact'] ?? '', 160);
$specialization = clean_text($_POST['specialization'] ?? '', 160);
$professionalRole = clean_text($_POST['professionalRole'] ?? '', 160);

$allowedBlood = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-', 'unknown'];

$errors = [];

if (!$isUser && !$isPersonnel) {
    $errors[] = 'Please choose a valid account type.';
}
if ($firstName === '' || $lastName === '' || $middleName === '') {
    $errors[] = 'First, middle, and last name are required.';
}
if ($username === '' || !preg_match('/^[A-Za-z0-9._]{3,50}$/', $username)) {
    $errors[] = 'Username must be 3-50 characters and may include letters, numbers, dots, and underscores.';
}
if ($address === '') {
    $errors[] = 'Address is required.';
}
if (!is_gmail($gmail)) {
    $errors[] = 'Please use a Gmail address (example@gmail.com).';
}
if (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters.';
}
if (!in_array($bloodType, $allowedBlood, true)) {
    $errors[] = 'Please choose a blood type.';
}
if (!$terms) {
    $errors[] = 'You must agree to the Terms and Conditions.';
}

if ($isPersonnel) {
    if ($companyName === '' || $companyId === '' || $companyAddress === '' || $companyContact === '') {
        $errors[] = 'Organization details are required for authorized personnel.';
    }
    if ($specialization === '' || $professionalRole === '') {
        $errors[] = 'Specialization and professional role are required.';
    }
    if (empty($_FILES['companyIdFile']['name'])) {
        $errors[] = 'Please upload a company or professional ID file.';
    }
}

if ($errors) {
    flash_set('error', implode(' ', $errors));
    redirect('SignUp.php');
}

$pdo = db();

$dup = $pdo->prepare('SELECT id FROM users WHERE username = ? OR gmail = ? LIMIT 1');
$dup->execute([$username, $gmail]);
if ($dup->fetch()) {
    flash_set('error', 'That username or Gmail address is already registered.');
    redirect('SignUp.php');
}

$idFilePath = '';
if ($isPersonnel) {
    $file = $_FILES['companyIdFile'];
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        flash_set('error', 'The ID file could not be uploaded.');
        redirect('SignUp.php');
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg', 'jpeg', 'png', 'pdf'], true)) {
        flash_set('error', 'ID file must be JPG, PNG, or PDF.');
        redirect('SignUp.php');
    }
    if ($file['size'] > 5 * 1024 * 1024) {
        flash_set('error', 'ID file must be 5MB or smaller.');
        redirect('SignUp.php');
    }
    $folder = __DIR__ . '/uploads/personnel_ids';
    if (!is_dir($folder)) {
        mkdir($folder, 0755, true);
    }
    $idFilePath = 'uploads/personnel_ids/' . bin2hex(random_bytes(8)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], __DIR__ . '/' . $idFilePath)) {
        flash_set('error', 'The ID file could not be saved.');
        redirect('SignUp.php');
    }
}

try {
    $pdo->beginTransaction();

    $publicId = generate_public_user_id($pdo);
    $role = $isPersonnel ? 'personnel' : 'user';
    $status = $isPersonnel ? 'pending_personnel' : 'active';
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare(
        'INSERT INTO users (public_user_id, username, gmail, password_hash, first_name, last_name, middle_name, suffix, address, blood_type, role, account_status)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $publicId,
        $username,
        $gmail,
        $hash,
        $firstName,
        $lastName,
        $middleName,
        $suffix,
        $address,
        $bloodType,
        $role,
        $status,
    ]);
    $userId = (int) $pdo->lastInsertId();

    $pdo->prepare('INSERT INTO user_profiles (user_id) VALUES (?)')->execute([$userId]);

    $token = generate_qr_token($pdo);
    $pdo->prepare('INSERT INTO qr_tokens (user_id, token, is_active) VALUES (?, ?, 1)')->execute([$userId, $token]);

    if ($isPersonnel) {
        $p = $pdo->prepare(
            'INSERT INTO personnel_profiles
            (user_id, organization_name, specialization, professional_role, professional_id_number, company_address, company_contact, company_id_file, review_status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $p->execute([
            $userId,
            $companyName,
            $specialization,
            $professionalRole,
            $companyId,
            $companyAddress,
            $companyContact,
            $idFilePath,
            'pending',
        ]);
        audit_log($pdo, $userId, 'personnel_registered', 'user', (string) $userId, 'Personnel application submitted');
    } else {
        audit_log($pdo, $userId, 'user_registered', 'user', (string) $userId, 'Regular user registered');
    }

    $pdo->commit();
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    flash_set('error', 'Registration could not be completed. Please try again.');
    redirect('SignUp.php');
}

$extra = $isPersonnel
    ? ' Your authorized-personnel application is pending review. You can log in with regular-user features while you wait.'
    : '';
flash_set('success', 'Account created. Your OMDS Account ID is ' . $publicId . '.' . $extra);
redirect('login.php');
