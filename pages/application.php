<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

$admin = require_admin();
$id = (int) ($_GET['id'] ?? 0);
$stmt = db()->prepare(
    'SELECT pp.*, u.first_name, u.last_name, u.middle_name, u.suffix, u.gmail, u.username,
            u.public_user_id, u.address, u.blood_type, u.account_status, u.id AS applicant_user_id
     FROM personnel_profiles pp
     JOIN users u ON u.id = pp.user_id
     WHERE pp.id = ?
     LIMIT 1'
);
$stmt->execute([$id]);
$row = $stmt->fetch();
if (!$row) {
    flash_set('error', 'Application not found.');
    redirect('inbox.php');
}

$reviewerName = '';
if ($row['reviewed_by']) {
    $r = db()->prepare('SELECT username FROM users WHERE id = ?');
    $r->execute([$row['reviewed_by']]);
    $reviewerName = (string) ($r->fetchColumn() ?: '');
}

admin_header('Application details', $admin);
?>
<div class="child-container3">
  <div class="column-title">Application #<?php echo (int) $row['id']; ?></div>
  <p class="notice-banner">This page shows registration and professional details only. Medical record files are not included.</p>
  <p><strong>Name:</strong> <?php echo e(trim($row['first_name'] . ' ' . $row['middle_name'] . ' ' . $row['last_name'] . ' ' . $row['suffix'])); ?></p>
  <p><strong>Username:</strong> <?php echo e($row['username']); ?></p>
  <p><strong>Account ID:</strong> <?php echo e($row['public_user_id']); ?></p>
  <p><strong>Gmail:</strong> <?php echo e($row['gmail']); ?></p>
  <p><strong>Address:</strong> <?php echo e($row['address']); ?></p>
  <p><strong>Blood type (from registration):</strong> <?php echo e($row['blood_type']); ?></p>
  <p><strong>Organization:</strong> <?php echo e($row['organization_name']); ?></p>
  <p><strong>Specialization:</strong> <?php echo e($row['specialization']); ?></p>
  <p><strong>Professional role:</strong> <?php echo e($row['professional_role']); ?></p>
  <p><strong>Professional ID number:</strong> <?php echo e($row['professional_id_number']); ?></p>
  <p><strong>Organization address:</strong> <?php echo e($row['company_address']); ?></p>
  <p><strong>Organization contact:</strong> <?php echo e($row['company_contact']); ?></p>
  <p><strong>Review status:</strong> <?php echo e($row['review_status']); ?></p>
  <p><strong>Account status:</strong> <?php echo e($row['account_status']); ?></p>
  <?php if ($row['reviewed_at']): ?>
    <p><strong>Reviewed by:</strong> <?php echo e($reviewerName); ?></p>
    <p><strong>Decision time:</strong> <?php echo e($row['reviewed_at']); ?></p>
  <?php endif; ?>
  <?php if ($row['denial_reason']): ?>
    <p><strong>Denial reason:</strong> <?php echo e($row['denial_reason']); ?></p>
  <?php endif; ?>
  <?php if ($row['company_id_file']): ?>
    <p>An ID document was uploaded with the application for review only. It is not a medical record file.</p>
    <p class="admin-actions">
      <a class="link-btn" href="view_id.php?id=<?php echo (int) $row['id']; ?>" target="_blank" rel="noopener">
        View uploaded professional ID
      </a>
    </p>
  <?php else: ?>
    <p><strong>Professional ID file:</strong> None on file for this application.</p>
  <?php endif; ?>

  <?php if ($row['review_status'] === 'pending'): ?>
    <?php if ((int) $row['applicant_user_id'] === (int) $admin['id']): ?>
      <p class="admin-error">You cannot approve your own application.</p>
    <?php else: ?>
      <div class="admin-actions">
        <form method="post" action="review.php">
          <?php echo csrf_field(); ?>
          <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
          <input type="hidden" name="decision" value="approve">
          <button type="submit">Approve</button>
        </form>
      </div>
      <form class="admin-form" method="post" action="review.php">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="id" value="<?php echo (int) $row['id']; ?>">
        <input type="hidden" name="decision" value="deny">
        <label>Denial reason</label>
        <textarea name="denial_reason" required placeholder="Explain the decision"></textarea>
        <button type="submit" class="deny-btn">Deny</button>
      </form>
    <?php endif; ?>
  <?php endif; ?>
  <p><a href="inbox.php">Back to inbox</a></p>
</div>
<?php admin_footer();
