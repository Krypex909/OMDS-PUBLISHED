<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

$admin = require_admin();
$stmt = db()->query(
    "SELECT pp.*, u.first_name, u.last_name, u.middle_name, u.suffix, u.gmail, u.username, u.public_user_id, u.account_status, u.created_at AS registered_at
     FROM personnel_profiles pp
     JOIN users u ON u.id = pp.user_id
     ORDER BY FIELD(pp.review_status, 'pending', 'approved', 'denied'), pp.created_at DESC"
);
$rows = $stmt->fetchAll();
admin_header('Request Inbox', $admin);
?>
<div class="child-container3">
  <div class="column-title">Authorized personnel applications</div>
  <p>Review applications only. This inbox does not open users' medical files.</p>
  <table class="admin-table">
    <tr>
      <th>Applicant</th>
      <th>Account ID</th>
      <th>Organization</th>
      <th>Status</th>
      <th></th>
    </tr>
    <?php if (!$rows): ?>
      <tr><td colspan="5">No applications yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
      <tr>
        <td><?php echo e(trim($row['first_name'] . ' ' . $row['last_name'])); ?></td>
        <td><?php echo e($row['public_user_id']); ?></td>
        <td><?php echo e($row['organization_name']); ?></td>
        <td><?php echo e($row['review_status']); ?></td>
        <td><a href="application.php?id=<?php echo (int) $row['id']; ?>">Details</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php admin_footer();
