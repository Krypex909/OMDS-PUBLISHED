<?php
require_once __DIR__ . '/../includes/access.php';
$user = require_login_ajax();
if (!personnel_can_access_records($user)) {
    http_response_code(403);
    exit('Authorized access is not available.');
}
$me = load_user_with_profile((int) $user['id']);
$stmt = db()->prepare(
    'SELECT ra.*, u.first_name, u.last_name, u.public_user_id, u.username
     FROM record_access ra
     JOIN users u ON u.id = ra.owner_user_id
     WHERE ra.grantee_user_id = ?
     ORDER BY ra.created_at DESC'
);
$stmt->execute([(int) $user['id']]);
$accessRows = $stmt->fetchAll();
?>
<head>
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css" />
  <link rel="stylesheet" href="css/AuthPersonnel/authorized_dashboard.css" />
  <link rel="stylesheet" href="css/admin.css" />
</head>
<div class="parent-container">
  <div class="child-container1">
    <div class="child-container3">
      <div class="column-title">Find a user</div>
      <p>Search does not open medical records by itself. After you find a user, request access. The user must grant it.</p>
      <form class="admin-form" method="get" action="scan.php">
        <input type="text" name="username" placeholder="Username">
        <input type="text" name="account_id" placeholder="10-digit Account ID" maxlength="10">
        <button class="link-btn" type="submit">Look up</button>
      </form>
    </div>

    <div class="child-container3">
      <div class="column-title">Access requests</div>
      <table class="admin-table">
        <tr><th>User</th><th>Account ID</th><th>Status</th><th>Purpose</th></tr>
        <?php if (!$accessRows): ?>
          <tr><td colspan="4">No requests yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($accessRows as $row): ?>
          <tr>
            <td><?php echo e(trim($row['first_name'] . ' ' . $row['last_name'])); ?></td>
            <td><?php echo e($row['public_user_id']); ?></td>
            <td><?php echo e($row['status']); ?></td>
            <td><?php echo e($row['purpose']); ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div class="child-container3">
      <div class="column-title">Authorized Personnel Credentials</div>
      <p><strong>Name:</strong> <?php echo e(display_name($me)); ?></p>
      <p><strong>Personnel Account ID:</strong> <?php echo e($me['public_user_id']); ?></p>
      <p><strong>Organization:</strong> <?php echo e($me['organization_name'] ?? ''); ?></p>
      <p><strong>Role:</strong> <?php echo e($me['professional_role'] ?? ''); ?></p>
      <p><strong>Specialization:</strong> <?php echo e($me['specialization'] ?? ''); ?></p>
      <p><strong>Status:</strong> Approved</p>
    </div>
  </div>
</div>
