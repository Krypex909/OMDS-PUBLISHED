<?php
require_once __DIR__ . '/../includes/access.php';
$user = require_login_ajax();
if (!personnel_can_access_records($user)) {
    http_response_code(403);
    exit('Authorized access is not available.');
}
$stmt = db()->prepare(
    'SELECT mf.*, u.first_name, u.last_name, u.public_user_id
     FROM medical_files mf
     JOIN record_access ra ON ra.owner_user_id = mf.owner_user_id AND ra.grantee_user_id = ? AND ra.status = "granted"
     JOIN users u ON u.id = mf.owner_user_id
     WHERE (ra.expires_at IS NULL OR ra.expires_at > NOW())
     ORDER BY mf.uploaded_at DESC'
);
$stmt->execute([(int) $user['id']]);
$files = $stmt->fetchAll();
?>
<head>
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css" />
</head>
<div class="parent-container">
  <div class="child-container1">
    <div class="child-container3">
      <div class="column-title">Files you may view</div>
      <p>Only files from users who currently grant you access.</p>
      <table class="admin-table">
        <tr><th>Owner</th><th>Account ID</th><th>Category</th><th>File</th></tr>
        <?php if (!$files): ?>
          <tr><td colspan="4">No shared files.</td></tr>
        <?php endif; ?>
        <?php foreach ($files as $file): ?>
          <tr>
            <td><?php echo e(trim($file['first_name'] . ' ' . $file['last_name'])); ?></td>
            <td><?php echo e($file['public_user_id']); ?></td>
            <td><?php echo e($file['category']); ?></td>
            <td><a href="view_file.php?id=<?php echo (int) $file['id']; ?>"><?php echo e($file['original_name']); ?></a></td>
          </tr>
        <?php endforeach; ?>
      </table>
    </div>
  </div>
</div>
