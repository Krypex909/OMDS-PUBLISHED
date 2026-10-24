<?php
require_once __DIR__ . '/../includes/access.php';
$user = require_login_ajax();
if (!personnel_can_access_records($user)) {
    http_response_code(403);
    exit('Authorized access is not available.');
}
$stmt = db()->prepare(
    'SELECT * FROM audit_logs
     WHERE actor_user_id = ?
       AND (action LIKE "%record%" OR action LIKE "%access%" OR action LIKE "qr_%")
     ORDER BY created_at DESC LIMIT 100'
);
$stmt->execute([(int) $user['id']]);
$rows = $stmt->fetchAll();
?>
<head>
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css" />
</head>
<div class="parent-container">
  <div class="child-container1">
    <div class="child-container3">
      <div class="column-title">Access History</div>
      <table class="admin-table">
        <tr><th>When</th><th>Action</th><th>Details</th></tr>
        <?php if (!$rows): ?>
          <tr><td colspan="3">No history yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td><?php echo e($row['created_at']); ?></td>
            <td><?php echo e($row['action']); ?></td>
            <td><?php echo e($row['details']); ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>
