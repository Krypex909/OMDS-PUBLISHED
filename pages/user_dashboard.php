<?php
require_once __DIR__ . '/../includes/access.php';
$user = require_login_ajax();
if (!can_use_user_shell($user)) {
    http_response_code(403);
    exit('Not allowed.');
}
$profile = load_user_with_profile((int) $user['id']);
$qrMedical = medical_qr_url((int) $user['id']);
$qrLoginData = $profile['public_user_id'];
$picture = $profile['profile_picture'] ?: 'images/User-Profile.jpg';
?>
<head>
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css" />
</head>
<div class="parent-container">
  <div class="child-container1">
    <div class="child-container3">
      <div class="column-title">Profile Information</div>
      <div class="profile-area">
        <img class="profile-picture" src="<?php echo e($picture); ?>" alt="Profile Picture" />
      </div>
      <div class="display-information-grid">
        <div class="display-information-item">
          <span class="display-label">Name</span>
          <span class="display-value"><?php echo e(display_name($profile)); ?></span>
        </div>
        <div class="display-information-item">
          <span class="display-label">Blood Type</span>
          <span class="display-value"><?php echo e($profile['blood_type'] ?: 'Not set'); ?></span>
        </div>
        <div class="display-information-item">
          <span class="display-label">Account Number</span>
          <span class="display-value"><?php echo e($profile['public_user_id']); ?></span>
        </div>
        <div class="display-information-item">
          <span class="display-label">Username</span>
          <span class="display-value"><?php echo e($profile['username']); ?></span>
        </div>
        <div class="display-information-item">
          <span class="display-label">Date of Birth</span>
          <span class="display-value"><?php echo e($profile['date_of_birth'] ?: 'Not set'); ?></span>
        </div>
        <div class="display-information-item">
          <span class="display-label">Sex</span>
          <span class="display-value"><?php echo e($profile['sex'] ?: 'Not set'); ?></span>
        </div>
        <div class="display-information-item">
          <span class="display-label">Religion</span>
          <span class="display-value"><?php echo e($profile['religion'] ?: 'Not set'); ?></span>
        </div>
        <div class="display-information-item display-full">
          <span class="display-label">Address</span>
          <span class="display-value"><?php echo e($profile['address'] ?: 'Not set'); ?></span>
        </div>
        <div class="display-information-item">
          <span class="display-label">Contact Number</span>
          <span class="display-value"><?php echo e($profile['contact_number'] ?: 'Not set'); ?></span>
        </div>
        <div class="display-information-item">
          <span class="display-label">Emergency Contact</span>
          <span class="display-value"><?php echo e($profile['emergency_contact'] ?: 'Not set'); ?></span>
        </div>
        <div class="display-information-item">
          <span class="display-label">Emergency Contact Number</span>
          <span class="display-value"><?php echo e($profile['emergency_phone'] ?: 'Not set'); ?></span>
        </div>
      </div>
      <p><a class="link-btn" href="pages/user_edit.php" onclick="event.preventDefault(); fetch('pages/user_edit.php').then(r=>r.text()).then(t=>document.getElementById('mainContent').innerHTML=t);">Edit profile</a></p>
    </div>

    <div class="child-container3 qr-box">
      <div class="column-title">Your OMDS QR Codes</div>
      <p>The medical QR code only contains a hidden token. It does not include your password, blood type, or records. Scanning it does not show records until a permitted person is logged in and you have granted access.</p>
      <p><strong>Medical access QR</strong></p>
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($qrMedical); ?>" alt="Medical access QR">
      <p><strong>Login helper QR</strong> (contains only your 10-digit Account ID)</p>
      <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($qrLoginData); ?>" alt="Login account ID QR">
    </div>

    <div class="child-container3">
      <div class="column-title">Medical Information</div>
      <div class="medical-section">
        <div class="sub-title">Medical Conditions</div>
        <div class="display-other"><?php echo e($profile['medical_conditions'] ?: 'None specified'); ?></div>
      </div>
      <div class="medical-section">
        <div class="sub-title">Allergies</div>
        <div class="display-other"><?php echo e($profile['allergies'] ?: 'None specified'); ?></div>
      </div>
      <div class="medical-section">
        <div class="sub-title">Current Medications</div>
        <div class="display-other"><?php echo e($profile['current_medications'] ?: 'None specified'); ?></div>
      </div>
      <div class="medical-section">
        <div class="sub-title">Medical History</div>
        <div class="display-other"><?php echo e($profile['medical_history'] ?: 'None specified'); ?></div>
      </div>
      <div class="medical-section">
        <div class="sub-title">General Medical Status</div>
        <div class="display-other"><?php echo e($profile['general_status'] ?: 'None specified'); ?></div>
      </div>
    </div>
  </div>
</div>
