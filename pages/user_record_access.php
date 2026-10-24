<?php
require_once __DIR__ . '/../includes/access.php';
$user = require_login_ajax();
$flash = flash_get();
$stmt = db()->prepare(
    'SELECT ra.*, u.first_name, u.last_name, u.username, u.public_user_id, u.role, u.account_status
     FROM record_access ra
     JOIN users u ON u.id = ra.grantee_user_id
     WHERE ra.owner_user_id = ?
     ORDER BY ra.created_at DESC'
);
$stmt->execute([(int) $user['id']]);
$rows = $stmt->fetchAll();
?>
<head>
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css" />
  <link rel="stylesheet" href="css/admin.css" />
</head>
<div class="parent-container">
  <div class="child-container1">
    <div class="child-container3">
      <div class="column-title">Record Access</div>
      <p>Grant or revoke medical-record access. Pending personnel cannot use access even if a row exists.</p>
      <?php if ($flash): ?>
        <p class="<?php echo $flash['type'] === 'error' ? 'admin-error' : 'admin-success'; ?>"><?php echo e($flash['message']); ?></p>
      <?php endif; ?>
      <?php if (!$rows): ?>
        <p>No access requests yet. Authorized personnel create a request when they scan your QR code.</p>
      <?php else: ?>
        <table class="admin-table">
          <tr><th>Personnel</th><th>Account ID</th><th>Purpose</th><th>Status</th><th>Actions</th></tr>
          <?php foreach ($rows as $row): ?>
            <tr>
              <td><?php echo e(trim($row['first_name'] . ' ' . $row['last_name'])); ?></td>
              <td><?php echo e($row['public_user_id']); ?></td>
              <td><?php echo e($row['purpose']); ?></td>
              <td><?php echo e($row['status']); ?></td>
              <td>
                <?php if (in_array($row['status'], ['pending', 'revoked', 'denied'], true)): ?>
                  <form method="post" action="actions/grant_access.php" style="display:inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="access_id" value="<?php echo (int) $row['id']; ?>">
                    <button type="submit" class="link-btn">Grant</button>
                  </form>
                <?php endif; ?>
                <?php if ($row['status'] === 'granted'): ?>
                  <form method="post" action="actions/revoke_access.php" style="display:inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="access_id" value="<?php echo (int) $row['id']; ?>">
                    <button type="submit" class="link-btn deny-btn">Revoke</button>
                  </form>
                <?php endif; ?>
                <?php if ($row['status'] === 'pending'): ?>
                  <form method="post" action="actions/deny_access.php" style="display:inline">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="access_id" value="<?php echo (int) $row['id']; ?>">
                    <button type="submit" class="link-btn deny-btn">Deny</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>
