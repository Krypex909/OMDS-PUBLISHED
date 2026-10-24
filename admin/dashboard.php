<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

$admin = require_admin();
$pdo = db();
$counts = [
    'users' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn(),
    'personnel' => (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'personnel'")->fetchColumn(),
    'pending' => (int) $pdo->query("SELECT COUNT(*) FROM personnel_profiles WHERE review_status = 'pending'")->fetchColumn(),
    'orgs' => (int) $pdo->query('SELECT COUNT(*) FROM organizations')->fetchColumn(),
];
admin_header('Admin dashboard', $admin);
?>
<div class="child-container3">
  <div class="column-title">Overview</div>
  <div class="stat-grid">
    <div class="stat-card">Regular users<strong><?php echo $counts['users']; ?></strong></div>
    <div class="stat-card">Personnel accounts<strong><?php echo $counts['personnel']; ?></strong></div>
    <div class="stat-card">Pending applications<strong><?php echo $counts['pending']; ?></strong></div>
    <div class="stat-card">Organizations<strong><?php echo $counts['orgs']; ?></strong></div>
  </div>
  <p><a class="link-btn" href="inbox.php">Open Request Inbox</a></p>
</div>
<?php admin_footer();
