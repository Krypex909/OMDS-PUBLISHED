<?php
require_once __DIR__ . '/../includes/access.php';
$user = require_login_ajax();
$stmt = db()->prepare(
    'SELECT a.*, u.first_name, u.last_name, u.public_user_id, u.username
     FROM audit_logs a
     LEFT JOIN users u ON u.id = a.actor_user_id
     WHERE a.target_type IN ("medical_record", "record_access") AND a.target_id = ?
     ORDER BY a.created_at DESC
     LIMIT 100'
);
$stmt->execute([(string) $user['id']]);
$rows = $stmt->fetchAll();
?>
<head>
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css" />
  <link rel="stylesheet" href="css/NormalUser/visitor_history.css" />
</head>
<div class="parent-container">
  <div class="child-container1">
    <div class="child-container3">
      <div class="column-title">Visitor History</div>
      <p>People who requested, received, or used access to your records.</p>
      <table class="admin-table">
        <tr><th>#</th><th>When</th><th>Who</th><th>Account ID</th><th>Action</th></tr>
        <?php if (!$rows): ?>
          <tr><td colspan="5">No access events yet.</td></tr>
        <?php endif; ?>
        <?php foreach ($rows as $i => $row): ?>
          <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo e($row['created_at']); ?></td>
            <td><?php echo e(trim($row['first_name'] . ' ' . $row['last_name']) ?: $row['username']); ?></td>
            <td><?php echo e($row['public_user_id']); ?></td>
            <td><?php echo e($row['action']); ?></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>
