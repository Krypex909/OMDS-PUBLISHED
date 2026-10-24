<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

$admin = require_admin();
$pageSize = 20;
$requestedPage = filter_var(
    $_GET['page'] ?? 1,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$page = $requestedPage === false ? 1 : $requestedPage;

$totalRows = (int) db()->query(
    "SELECT COUNT(*)
     FROM personnel_profiles pp
     JOIN users u ON u.id = pp.user_id"
)->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

$stmt = db()->query(
    "SELECT pp.*, u.first_name, u.last_name, u.middle_name, u.suffix, u.gmail, u.username, u.public_user_id, u.account_status, u.created_at AS registered_at
     FROM personnel_profiles pp
     JOIN users u ON u.id = pp.user_id
     ORDER BY FIELD(pp.review_status, 'pending', 'approved', 'denied'), pp.created_at DESC
     LIMIT " . $pageSize . ' OFFSET ' . $offset
);
$rows = $stmt->fetchAll();
admin_header('Request Inbox', $admin);
?>
<div class="child-container3">
  <div class="column-title">Authorized personnel applications</div>
  <p>Review applications only. This inbox does not open users' medical files.</p>
  <?php if ($totalRows > 0): ?>
    <p>Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $pageSize, $totalRows); ?> of <?php echo $totalRows; ?> applications.</p>
  <?php else: ?>
    <p>No applications to show.</p>
  <?php endif; ?>
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
  <?php if ($totalPages > 1): ?>
    <?php
      $firstVisiblePage = max(1, $page - 2);
      $lastVisiblePage = min($totalPages, $page + 2);
    ?>
    <nav class="admin-pagination" aria-label="Request inbox pages">
      <?php if ($page > 1): ?>
        <a href="inbox.php?page=<?php echo $page - 1; ?>" aria-label="Previous page">Previous</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Previous</span>
      <?php endif; ?>

      <?php if ($firstVisiblePage > 1): ?>
        <a href="inbox.php?page=1">1</a>
        <?php if ($firstVisiblePage > 2): ?><span aria-hidden="true">…</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($visiblePage = $firstVisiblePage; $visiblePage <= $lastVisiblePage; $visiblePage++): ?>
        <?php if ($visiblePage === $page): ?>
          <span class="current" aria-current="page"><?php echo $visiblePage; ?></span>
        <?php else: ?>
          <a href="inbox.php?page=<?php echo $visiblePage; ?>"><?php echo $visiblePage; ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($lastVisiblePage < $totalPages): ?>
        <?php if ($lastVisiblePage < $totalPages - 1): ?><span aria-hidden="true">…</span><?php endif; ?>
        <a href="inbox.php?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="inbox.php?page=<?php echo $page + 1; ?>" aria-label="Next page">Next</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Next</span>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>
<?php admin_footer();
