<?php
require_once __DIR__ . '/../includes/access.php';
$user = require_login_ajax();
if (!can_use_user_shell($user)) {
    http_response_code(403);
    exit('Not allowed.');
}
$flash = flash_get();
$stmt = db()->prepare('SELECT * FROM medical_files WHERE owner_user_id = ? ORDER BY uploaded_at DESC');
$stmt->execute([(int) $user['id']]);
$files = $stmt->fetchAll();
$categories = [
    'Physical Examination Record',
    'Pre-Employment Medical Examination',
    'Annual Medical Examination',
    'Laboratory Result',
    'Prescription',
    'Other Medical Document',
];
?>
<head>
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css" />
  <link rel="stylesheet" href="css/NormalUser/upload.css" />
</head>
<div class="parent-container">
  <div class="child-container1">
    <div class="child-container3">
      <div class="column-title">Medical Documents</div>
      <p>Upload files to your OMDS account. Only you can see them unless you grant access to approved authorized personnel.</p>
      <?php if ($flash): ?>
        <p class="<?php echo $flash['type'] === 'error' ? 'admin-error' : 'admin-success'; ?>"><?php echo e($flash['message']); ?></p>
      <?php endif; ?>
      <form class="admin-form" method="post" action="actions/upload_medical.php" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <label>Category</label>
        <select name="category" required>
          <?php foreach ($categories as $cat): ?>
            <option value="<?php echo e($cat); ?>"><?php echo e($cat); ?></option>
          <?php endforeach; ?>
        </select>
        <label>File (PDF, JPG, PNG — max 8MB)</label>
        <input type="file" name="medical_file" accept=".pdf,.jpg,.jpeg,.png" required>
        <button class="link-btn" type="submit">Upload</button>
      </form>

      <div class="column-title">Uploaded files</div>
      <?php if (!$files): ?>
        <p>No files uploaded yet.</p>
      <?php else: ?>
        <table class="admin-table">
          <tr><th>Category</th><th>File</th><th>Uploaded</th><th></th></tr>
          <?php foreach ($files as $file): ?>
            <tr>
              <td><?php echo e($file['category']); ?></td>
              <td><?php echo e($file['original_name']); ?></td>
              <td><?php echo e($file['uploaded_at']); ?></td>
              <td><a href="view_file.php?id=<?php echo (int) $file['id']; ?>">View</a></td>
            </tr>
          <?php endforeach; ?>
        </table>
      <?php endif; ?>
    </div>
  </div>
</div>
