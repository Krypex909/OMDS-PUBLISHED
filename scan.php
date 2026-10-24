<?php
require_once __DIR__ . '/includes/access.php';

$user = require_login();
$pdo = db();

$token = clean_text($_GET['t'] ?? '', 64);
$username = clean_text($_GET['username'] ?? '', 50);
$accountId = preg_replace('/\D/', '', $_GET['account_id'] ?? '');
$owner = null;
$lookupNote = '';

if ($token !== '') {
    $stmt = $pdo->prepare(
        'SELECT u.id FROM qr_tokens qt JOIN users u ON u.id = qt.user_id WHERE qt.token = ? AND qt.is_active = 1 LIMIT 1'
    );
    $stmt->execute([$token]);
    $found = $stmt->fetch();
    if ($found) {
        $owner = load_user_with_profile((int) $found['id']);
        audit_log($pdo, (int) $user['id'], 'qr_scanned', 'medical_record', (string) $owner['id'], 'Opaque QR token presented');
    } else {
        $lookupNote = 'That QR code is not valid.';
    }
} elseif ($username !== '' || strlen($accountId) === 10) {
    if (strlen($accountId) === 10) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE public_user_id = ? LIMIT 1');
        $stmt->execute([$accountId]);
    } else {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
    }
    $found = $stmt->fetch();
    if ($found) {
        $owner = load_user_with_profile((int) $found['id']);
        audit_log($pdo, (int) $user['id'], 'user_lookup', 'user', (string) $owner['id'], 'Username or account ID lookup');
    } else {
        $lookupNote = 'No matching user was found.';
    }
}

$canSeeRecords = false;
$accessRow = null;
$reason = '';

if ($owner) {
    if ((int) $owner['id'] === (int) $user['id']) {
        $reason = 'This is your own QR code. Open your dashboard to view your records. Scanning does not display medical data by itself.';
    } elseif (($user['role'] ?? '') === 'admin') {
        $reason = 'Administrators can confirm that an application or account exists, but this screen does not open the user’s medical records.';
    } elseif (($user['role'] ?? '') === 'organization') {
        $reason = 'Organization accounts cannot open personal medical records from a QR scan.';
    } elseif (!personnel_can_access_records($user)) {
        $reason = 'Your account does not have authorized personnel privileges. Pending or denied applicants cannot open protected medical records.';
        audit_log($pdo, (int) $user['id'], 'record_access_denied', 'medical_record', (string) $owner['id'], 'Scanner lacked approved personnel status');
    } else {
        $accessRow = current_access_row($pdo, (int) $owner['id'], (int) $user['id']);
        if (has_granted_record_access($pdo, (int) $owner['id'], (int) $user['id'])) {
            $canSeeRecords = true;
            audit_log($pdo, (int) $user['id'], 'record_viewed', 'medical_record', (string) $owner['id'], 'Granted personnel viewed record summary');
        } else {
            $reason = 'You are identified, but the user has not granted record access (or access was revoked). You may request access below.';
        }
    }
}

$files = [];
if ($canSeeRecords && $owner) {
    $fs = $pdo->prepare('SELECT * FROM medical_files WHERE owner_user_id = ? ORDER BY uploaded_at DESC');
    $fs->execute([(int) $owner['id']]);
    $files = $fs->fetchAll();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>OMDS Secure Scan</title>
  <link rel="stylesheet" href="css/main.css">
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css">
  <link rel="stylesheet" href="css/admin.css">
</head>
<body>
<div class="admin-content" style="max-width:900px;margin:20px auto;">
  <p><a href="<?php echo e(home_path_for($user)); ?>">Back to dashboard</a></p>
  <div class="child-container3">
    <div class="column-title">Secure record lookup</div>
    <p>This page never shows a password. Medical details appear only after identity, authorization, and user permission are checked on the server.</p>

    <form class="admin-form" method="get" action="scan.php">
      <label>Opaque QR token (from medical QR URL)</label>
      <input type="text" name="t" value="<?php echo e($token); ?>" placeholder="Paste token if needed">
      <label>Username</label>
      <input type="text" name="username" value="<?php echo e($username); ?>">
      <label>Account ID</label>
      <input type="text" name="account_id" value="<?php echo e($accountId); ?>" maxlength="10">
      <button class="link-btn" type="submit">Check</button>
    </form>

    <?php if ($lookupNote): ?>
      <p class="admin-error"><?php echo e($lookupNote); ?></p>
    <?php endif; ?>

    <?php if ($owner): ?>
      <h3>Account found</h3>
      <p><strong>Name:</strong> <?php echo e(display_name($owner)); ?></p>
      <p><strong>Account ID:</strong> <?php echo e($owner['public_user_id']); ?></p>
      <p><strong>Username:</strong> <?php echo e($owner['username']); ?></p>

      <?php if ($reason): ?>
        <p class="notice-banner"><?php echo e($reason); ?></p>
      <?php endif; ?>

      <?php if ($canSeeRecords): ?>
        <h3>Permitted medical summary</h3>
        <p><strong>Blood type:</strong> <?php echo e($owner['blood_type']); ?></p>
        <p><strong>Conditions:</strong> <?php echo e($owner['medical_conditions'] ?: 'None specified'); ?></p>
        <p><strong>Allergies:</strong> <?php echo e($owner['allergies'] ?: 'None specified'); ?></p>
        <p><strong>Medications:</strong> <?php echo e($owner['current_medications'] ?: 'None specified'); ?></p>
        <p><strong>History:</strong> <?php echo e($owner['medical_history'] ?: 'None specified'); ?></p>
        <p><strong>Status:</strong> <?php echo e($owner['general_status'] ?: 'None specified'); ?></p>
        <h4>Files</h4>
        <?php if (!$files): ?>
          <p>No uploaded files.</p>
        <?php else: ?>
          <ul>
            <?php foreach ($files as $file): ?>
              <li><a href="view_file.php?id=<?php echo (int) $file['id']; ?>"><?php echo e($file['category'] . ' — ' . $file['original_name']); ?></a></li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      <?php elseif (personnel_can_access_records($user) && (int) $owner['id'] !== (int) $user['id']): ?>
        <form class="admin-form" method="post" action="actions/request_access.php">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="owner_id" value="<?php echo (int) $owner['id']; ?>">
          <label>Purpose of access</label>
          <input type="text" name="purpose" required placeholder="e.g. Medical examination">
          <button class="link-btn" type="submit">Request access from this user</button>
        </form>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
