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

$totalRows = (int) db()->query("SELECT COUNT(*) FROM users WHERE role <> 'admin'")->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

$stmt = db()->query(
    "SELECT u.id, u.public_user_id, u.username, u.gmail, u.first_name, u.last_name,
            u.role, u.account_status, u.is_disabled, u.deleted_at, u.created_at
     FROM users u
     WHERE u.role <> 'admin'
     ORDER BY u.created_at DESC, u.id DESC"
     . ' LIMIT ' . $pageSize . ' OFFSET ' . $offset
);
$rows = $stmt->fetchAll();

admin_header('User Management', $admin);
?>
<header>
  <style>
  </style>
</header>
<div class="child-container3">
  <div class="column-title">User Management</div>
  <p>This list shows account details only. It does not display medical records. Administrator accounts are excluded.</p>
  <?php if ($totalRows > 0): ?>
    <p>Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $pageSize, $totalRows); ?> of <?php echo $totalRows; ?> accounts.</p>
  <?php else: ?>
    <p>No accounts to show.</p>
  <?php endif; ?>
  <table class="admin-table">
    <tr>
      <th>Name / Username</th>
      <th>Account ID</th>
      <th>Email</th>
      <th>Role</th>
      <th>Status</th>
      <th>Created</th>
      <th>Actions</th>
    </tr>
    <?php if (!$rows): ?>
      <tr><td colspan="7">No non-administrator accounts found.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
      <?php
        $name = trim($row['first_name'] . ' ' . $row['last_name']);
        $status = !empty($row['deleted_at'])
            ? 'Soft deleted'
            : (!empty($row['is_disabled'])
                ? 'Disabled'
                : ucwords(str_replace('_', ' ', $row['account_status'])));
      ?>
      <tr>
        <td>
          <?php echo e($name !== '' ? $name : $row['username']); ?><br>
          <small><?php echo e($row['username']); ?></small>
        </td>
        <td><?php echo e($row['public_user_id']); ?></td>
        <td><?php echo e($row['gmail']); ?></td>
        <td><?php echo e(ucfirst($row['role'])); ?></td>
        <td><?php echo e($status); ?></td>
        <td><?php echo e($row['created_at']); ?></td>
        <td>
          <div class="admin-user-actions">
            <?php if (!empty($row['deleted_at'])): ?>
              <form method="post" action="user_action.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?php echo (int) $row['id']; ?>">
                <input type="hidden" name="action" value="restore">
                <button type="submit">Restore</button>
              </form>
            <?php elseif (!empty($row['is_disabled'])): ?>
              <form method="post" action="user_action.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?php echo (int) $row['id']; ?>">
                <input type="hidden" name="action" value="enable">
                <button type="submit">Enable</button>
              </form>
            <?php else: ?>
              <form style="display: inline-block;" method="post" action="user_action.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="user_id" value="<?php echo (int) $row['id']; ?>">
                <input type="hidden" name="action" value="disable">
                <button style="padding: 5px;
                padding-left: 10px;
                padding-right: 10px;
                margin-bottom: 5px;" type="submit">Disable</button>
              </form>
            <?php endif; ?>

          </div>
        </td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php if ($totalPages > 1): ?>
    <?php
      $firstVisiblePage = max(1, $page - 2);
      $lastVisiblePage = min($totalPages, $page + 2);
    ?>
    <nav class="admin-pagination" aria-label="User management pages">
      <?php if ($page > 1): ?>
        <a href="users.php?page=<?php echo $page - 1; ?>" aria-label="Previous page">Previous</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Previous</span>
      <?php endif; ?>

      <?php if ($firstVisiblePage > 1): ?>
        <a href="users.php?page=1">1</a>
        <?php if ($firstVisiblePage > 2): ?><span aria-hidden="true">…</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($visiblePage = $firstVisiblePage; $visiblePage <= $lastVisiblePage; $visiblePage++): ?>
        <?php if ($visiblePage === $page): ?>
          <span class="current" aria-current="page"><?php echo $visiblePage; ?></span>
        <?php else: ?>
          <a href="users.php?page=<?php echo $visiblePage; ?>"><?php echo $visiblePage; ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($lastVisiblePage < $totalPages): ?>
        <?php if ($lastVisiblePage < $totalPages - 1): ?><span aria-hidden="true">…</span><?php endif; ?>
        <a href="users.php?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="users.php?page=<?php echo $page + 1; ?>" aria-label="Next page">Next</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Next</span>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>
<?php admin_footer();
