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

$totalRows = (int) db()->query('SELECT COUNT(*) FROM audit_logs')->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

if ($totalRows > 0) {
    $stmt = db()->query(
    'SELECT a.id, a.actor_user_id, a.action, a.target_type, a.target_id, a.details, a.created_at,
            u.username, u.public_user_id, u.first_name, u.last_name
     FROM audit_logs a
     LEFT JOIN users u ON u.id = a.actor_user_id
     ORDER BY a.id DESC
     LIMIT ' . $pageSize . ' OFFSET ' . $offset
    );
    $rows = $stmt->fetchAll();
} else {
    $rows = [];
}

admin_header('Audit Logs', $admin);
?>
<div class="child-container3">
  <div class="column-title">Audit Logs</div>
  <p>Administrative and account events already stored by OMDS. This page does not open medical record files.</p>
  <?php if ($totalRows > 0): ?>
    <p>Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $pageSize, $totalRows); ?> of <?php echo $totalRows; ?> audit entries.</p>
  <?php else: ?>
    <p>No audit entries to show.</p>
  <?php endif; ?>
  <table class="admin-table">
    <tr>
      <th>When</th>
      <th>Actor</th>
      <th>Action</th>
      <th>Target</th>
      <th>Details</th>
    </tr>
    <?php if (!$rows): ?>
      <tr><td colspan="5">No audit events yet.</td></tr>
    <?php endif; ?>
    <?php foreach ($rows as $row): ?>
      <?php
        $actorLabel = trim((string) $row['username']);
        if ($actorLabel === '') {
            $actorLabel = $row['actor_user_id'] ? ('User #' . (int) $row['actor_user_id']) : 'System';
        }
        if (!empty($row['public_user_id'])) {
            $actorLabel .= ' (' . $row['public_user_id'] . ')';
        }
        $targetLabel = trim($row['target_type'] . ' ' . $row['target_id']);
      ?>
      <tr>
        <td><?php echo e($row['created_at']); ?></td>
        <td><?php echo e($actorLabel); ?></td>
        <td><?php echo e($row['action']); ?></td>
        <td>
          <?php if ($row['target_type'] === 'personnel_profile' && ctype_digit((string) $row['target_id'])): ?>
            <a href="application.php?id=<?php echo (int) $row['target_id']; ?>">
              <?php echo e($targetLabel); ?>
            </a>
          <?php else: ?>
            <?php echo e($targetLabel); ?>
          <?php endif; ?>
        </td>
        <td><?php echo e($row['details']); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php if ($totalPages > 1): ?>
    <?php
      $firstVisiblePage = max(1, $page - 2);
      $lastVisiblePage = min($totalPages, $page + 2);
    ?>
    <nav class="admin-pagination" aria-label="Audit log pages">
      <?php if ($page > 1): ?>
        <a href="audit.php?page=<?php echo $page - 1; ?>" aria-label="Previous page">Previous</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Previous</span>
      <?php endif; ?>

      <?php if ($firstVisiblePage > 1): ?>
        <a href="audit.php?page=1">1</a>
        <?php if ($firstVisiblePage > 2): ?><span aria-hidden="true">…</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($visiblePage = $firstVisiblePage; $visiblePage <= $lastVisiblePage; $visiblePage++): ?>
        <?php if ($visiblePage === $page): ?>
          <span class="current" aria-current="page"><?php echo $visiblePage; ?></span>
        <?php else: ?>
          <a href="audit.php?page=<?php echo $visiblePage; ?>"><?php echo $visiblePage; ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($lastVisiblePage < $totalPages): ?>
        <?php if ($lastVisiblePage < $totalPages - 1): ?><span aria-hidden="true">…</span><?php endif; ?>
        <a href="audit.php?page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="audit.php?page=<?php echo $page + 1; ?>" aria-label="Next page">Next</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Next</span>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
</div>
<?php admin_footer();
