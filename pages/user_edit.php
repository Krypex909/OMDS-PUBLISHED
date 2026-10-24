<?php
require_once __DIR__ . '/../includes/access.php';
$user = require_login_ajax();
if (!can_use_user_shell($user)) {
    http_response_code(403);
    exit('Not allowed.');
}
$profile = load_user_with_profile((int) $user['id']);
?>
<head>
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css" />
  <link rel="stylesheet" href="css/NormalUser/edit_profile.css" />
</head>
<div class="parent-container">
  <div class="child-container1">
    <div class="child-container3">
      <div class="column-title">Edit User Profile</div>
      <form class="admin-form" method="post" action="actions/save_profile.php" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-group">
          <label>First Name</label>
          <input type="text" name="first_name" value="<?php echo e($profile['first_name']); ?>" required>
        </div>
        <div class="form-group">
          <label>Middle Name</label>
          <input type="text" name="middle_name" value="<?php echo e($profile['middle_name']); ?>">
        </div>
        <div class="form-group">
          <label>Last Name</label>
          <input type="text" name="last_name" value="<?php echo e($profile['last_name']); ?>" required>
        </div>
        <div class="form-group">
          <label>Suffix</label>
          <input type="text" name="suffix" value="<?php echo e($profile['suffix']); ?>">
        </div>
        <div class="form-group">
          <label>Blood Type</label>
          <input type="text" name="blood_type" value="<?php echo e($profile['blood_type']); ?>">
        </div>
        <div class="form-group">
          <label>Date of Birth</label>
          <input type="date" name="date_of_birth" value="<?php echo e($profile['date_of_birth']); ?>">
        </div>
        <div class="form-group">
          <label>Sex</label>
          <input type="text" name="sex" value="<?php echo e($profile['sex']); ?>">
        </div>
        <div class="form-group">
          <label>Religion</label>
          <input type="text" name="religion" value="<?php echo e($profile['religion']); ?>">
        </div>
        <div class="form-group">
          <label>Address</label>
          <textarea name="address"><?php echo e($profile['address']); ?></textarea>
        </div>
        <div class="form-group">
          <label>Contact Number</label>
          <input type="text" name="contact_number" value="<?php echo e($profile['contact_number']); ?>">
        </div>
        <div class="form-group">
          <label>Emergency Contact</label>
          <input type="text" name="emergency_contact" value="<?php echo e($profile['emergency_contact']); ?>">
        </div>
        <div class="form-group">
          <label>Emergency Contact Number</label>
          <input type="text" name="emergency_phone" value="<?php echo e($profile['emergency_phone']); ?>">
        </div>
        <div class="form-group">
          <label>Medical Conditions</label>
          <textarea name="medical_conditions"><?php echo e($profile['medical_conditions']); ?></textarea>
        </div>
        <div class="form-group">
          <label>Allergies</label>
          <textarea name="allergies"><?php echo e($profile['allergies']); ?></textarea>
        </div>
        <div class="form-group">
          <label>Current Medications</label>
          <textarea name="current_medications"><?php echo e($profile['current_medications']); ?></textarea>
        </div>
        <div class="form-group">
          <label>Medical History</label>
          <textarea name="medical_history"><?php echo e($profile['medical_history']); ?></textarea>
        </div>
        <div class="form-group">
          <label>General Medical Status</label>
          <textarea name="general_status"><?php echo e($profile['general_status']); ?></textarea>
        </div>
        <div class="form-group">
          <label>Profile picture (optional)</label>
          <input type="file" name="profile_picture" accept="image/*">
        </div>
        <button type="submit" class="link-btn">Save profile</button>
      </form>
    </div>
  </div>
</div>
