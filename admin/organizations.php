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

$totalRows = (int) db()->query('SELECT COUNT(*) FROM organizations')->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

$stmt = db()->query(
    'SELECT o.id, o.name, o.address, o.status, o.created_at,
            u.username, u.public_user_id, u.account_status, u.is_disabled, u.deleted_at
     FROM organizations o
     JOIN users u ON u.id = o.user_id
     ORDER BY o.created_at DESC, o.id DESC
     LIMIT ' . $pageSize . ' OFFSET ' . $offset
);
$rows = $stmt->fetchAll();

admin_header('Organizations', $admin);
?>
<div class="child-container3">
  <div class="column-title">Organizations</div>
  <p>This list shows organization and account details only. Open an organization to view its members; medical records are not shown here.</p>
  <?php if ($totalRows > 0): ?>
    <p>Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $pageSize, $totalRows); ?> of <?php echo $totalRows; ?> organizations.</p>
  <?php else: ?>
    <p>No organizations to show. Organization accounts are managed by an administrator and are not created through public registration.</p>
  <?php endif; ?>
  <table class="admin-table">
    <tr>
      <th>Organization</th>
      <th>Address</th>
      <th>Organization status</th>
      <th>Account status</th>
      <th>Created</th>
      <th></th>
    </tr>
    <?php if (!$rows): ?>
      <tr><td colspan="6">No organizations found.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
      <?php
        $accountStatus = !empty($row['deleted_at'])
            ? 'Deleted'
            : (!empty($row['is_disabled'])
                ? 'Disabled'
                : ucwords(str_replace('_', ' ', $row['account_status'])));
      ?>
      <tr>
        <td>
          <?php echo e($row['name']); ?><br>
          <small><?php echo e($row['username']); ?> · <?php echo e($row['public_user_id']); ?></small>
        </td>
        <td><?php echo e($row['address']); ?></td>
        <td><?php echo e(ucfirst($row['status'])); ?></td>
        <td><?php echo e($accountStatus); ?></td>
        <td><?php echo e($row['created_at']); ?></td>
        <td><a href="organization_members.php?id=<?php echo (int) $row['id']; ?>">View members</a></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php if ($totalPages > 1): ?>
    <?php
      $firstVisiblePage = max(1, $page - 2);
      $lastVisiblePage = min($totalPages, $page + 2);
    ?>
    <nav class="admin-pagination" aria-label="Organization pages">
      <?php if ($page > 1): ?>
        <a href="organizations.php?page=<?php echo $page - 1; ?>" aria-label="Previous page">Previous</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Previous</span>
      <?php endif; ?>

      <?php if ($firstVisiblePage > 1): ?>
        <a href="organizations.php?page=1">1</a>
        <?php if ($firstVisiblePage > 2): ?><span aria-hidden="true">…</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($visiblePage = $firstVisiblePage; $visiblePage <= $lastVisiblePage; $visiblePage++): ?>
        <?php if ($visiblePage === $page): ?>
          <span class="current" aria-current="page"><?php echo $visiblePage; ?></span>
        <?php else: ?>
          <a href="organizations.php?page=<?php echo $visiblePage; ?>"><?php echo $visiblePage; ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($lastVisiblePage < $totalPages): ?>
        <?php if ($lastVisiblePage < $totalPages - 1): ?><span aria-hidden="true">…</span><?php endif; ?>
        <a href="organizations.php?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="organizations.php?page=<?php echo $page + 1; ?>" aria-label="Next page">Next</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Next</span>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>
<?php admin_footer();
