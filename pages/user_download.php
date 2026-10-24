<?php
require_once __DIR__ . '/../includes/access.php';
$user = require_login_ajax();
$profile = load_user_with_profile((int) $user['id']);
$qrMedical = medical_qr_url((int) $user['id']);
?>
<head>
  <link rel="stylesheet" href="css/NormalUser/User_download.css" />
  <link rel="stylesheet" href="css/NormalUser/dashCSS2.css" />
</head>
<div class="parent-container">
  <div class="child-container1">
    <div class="child-container3">
      <div class="download-page">
        <div class="download-header">
          <h2>Download Files</h2>
          <p>Your live account number, QR token, and uploaded files.</p>
        </div>
        <div class="download-section">
          <div class="section-title">Digital OMDS ID</div>
          <div class="account-sheet">
            <div class="account-row"><strong>Account Number</strong><span><?php echo e($profile['public_user_id']); ?></span></div>
            <div class="account-row"><strong>Username</strong><span><?php echo e($profile['username']); ?></span></div>
            <div class="account-row"><strong>Name</strong><span><?php echo e(display_name($profile)); ?></span></div>
          </div>
          <div class="qr-box">
            <p>Medical QR (opaque token only)</p>
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data=<?php echo urlencode($qrMedical); ?>" alt="QR">
          </div>
        </div>
        <div class="download-section">
          <div class="section-title">Uploaded medical files</div>
          <p>Open Upload Medical File to view or download files stored on the server.</p>
        </div>
      </div>
    </div>
  </div>
</div>
