<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

$admin = require_admin();

$stmt = db()->query(
    'SELECT a.id, a.actor_user_id, a.action, a.target_type, a.target_id, a.details, a.created_at,
            u.username, u.public_user_id, u.first_name, u.last_name
     FROM audit_logs a
     LEFT JOIN users u ON u.id = a.actor_user_id
     ORDER BY a.id DESC
     LIMIT 200'
);
$rows = $stmt->fetchAll();

admin_header('Audit Logs', $admin);
?>
<div class="child-container3">
  <div class="column-title">Audit Logs</div>
  <p>Administrative and account events already stored by OMDS. This page does not open medical record files.</p>
  <p>Showing the 200 most recent entries.</p>
  <table class="admin-table">
    <tr>
      <th>When</th>
      <th>Actor</th>
      <th>Action</th>
      <th>Target</th>
      <th>Details</th>
    </tr>
    <?php if (!$rows): ?>
      <tr><td colspan="5">No audit events yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
      <?php
        $actorLabel = trim((string) $row['username']);
        if ($actorLabel === '') {
            $actorLabel = $row['actor_user_id'] ? ('User #' . (int) $row['actor_user_id']) : 'System';
        }
        if (!empty($row['public_user_id'])) {
            $actorLabel .= ' (' . $row['public_user_id'] . ')';
        }
        $targetLabel = trim($row['target_type'] . ' ' . $row['target_id']);
      ?>
      <tr>
        <td><?php echo e($row['created_at']); ?></td>
        <td><?php echo e($actorLabel); ?></td>
        <td><?php echo e($row['action']); ?></td>
        <td>
          <?php if ($row['target_type'] === 'personnel_profile' && ctype_digit((string) $row['target_id'])): ?>
            <a href="application.php?id=<?php echo (int) $row['target_id']; ?>">
              <?php echo e($targetLabel); ?>
            </a>
          <?php else: ?>
            <?php echo e($targetLabel); ?>
          <?php endif; ?>
        </td>
        <td><?php echo e($row['details']); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php admin_footer();
