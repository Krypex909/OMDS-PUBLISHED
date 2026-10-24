<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin_layout.php';

$admin = require_admin();
$organizationId = filter_var(
    $_GET['id'] ?? null,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);

if ($organizationId === false) {
    flash_set('error', 'Choose an organization to view its members.');
    redirect('organizations.php');
}

$pdo = db();
$organizationStmt = $pdo->prepare(
    'SELECT o.id, o.name, o.address, o.status, u.username, u.public_user_id
     FROM organizations o
     JOIN users u ON u.id = o.user_id
     WHERE o.id = ?
     LIMIT 1'
);
$organizationStmt->execute([$organizationId]);
$organization = $organizationStmt->fetch();

if (!$organization) {
    flash_set('error', 'Organization not found.');
    redirect('organizations.php');
}

$pageSize = 20;
$requestedPage = filter_var(
    $_GET['page'] ?? 1,
    FILTER_VALIDATE_INT,
    ['options' => ['min_range' => 1]]
);
$page = $requestedPage === false ? 1 : $requestedPage;

$countStmt = $pdo->prepare('SELECT COUNT(*) FROM organization_members WHERE organization_id = ?');
$countStmt->execute([$organizationId]);
$totalRows = (int) $countStmt->fetchColumn();
$totalPages = max(1, (int) ceil($totalRows / $pageSize));
$page = min($page, $totalPages);
$offset = ($page - 1) * $pageSize;

$membersStmt = $pdo->prepare(
    'SELECT u.public_user_id, u.username, u.first_name, u.last_name, u.gmail, u.role,
            u.account_status, u.is_disabled, u.deleted_at, om.member_role, om.created_at
     FROM organization_members om
     JOIN users u ON u.id = om.user_id
     WHERE om.organization_id = ?
     ORDER BY om.created_at DESC, om.id DESC
     LIMIT ' . $pageSize . ' OFFSET ' . $offset
);
$membersStmt->execute([$organizationId]);
$members = $membersStmt->fetchAll();

admin_header('Organization members', $admin);
?>
<div class="child-container3">
  <div class="column-title"><?php echo e($organization['name']); ?> — Members</div>
  <p>
    Organization ID: <?php echo (int) $organization['id']; ?> |
    Account: <?php echo e($organization['username']); ?> (<?php echo e($organization['public_user_id']); ?>) |
    Status: <?php echo e(ucfirst($organization['status'])); ?>
  </p>
  <p>This page shows membership and basic account details only. It does not show medical records.</p>
  <?php if ($totalRows > 0): ?>
    <p>Showing <?php echo $offset + 1; ?>–<?php echo min($offset + $pageSize, $totalRows); ?> of <?php echo $totalRows; ?> members.</p>
  <?php else: ?>
    <p>This organization has no members yet.</p>
  <?php endif; ?>
  <table class="admin-table">
    <tr>
      <th>Member</th>
      <th>Account ID</th>
      <th>Email</th>
      <th>Account type</th>
      <th>Membership role</th>
      <th>Account status</th>
      <th>Added</th>
    </tr>
    <?php if (!$members): ?>
      <tr><td colspan="7">No members found.</td></tr>
    <?php endif; ?>
    <?php foreach ($members as $member): ?>
      <?php
        $name = trim($member['first_name'] . ' ' . $member['last_name']);
        $accountStatus = !empty($member['deleted_at'])
            ? 'Deleted'
            : (!empty($member['is_disabled'])
                ? 'Disabled'
                : ucwords(str_replace('_', ' ', $member['account_status'])));
      ?>
      <tr>
        <td><?php echo e($name !== '' ? $name : $member['username']); ?></td>
        <td><?php echo e($member['public_user_id']); ?></td>
        <td><?php echo e($member['gmail']); ?></td>
        <td><?php echo e(ucfirst($member['role'])); ?></td>
        <td><?php echo e($member['member_role']); ?></td>
        <td><?php echo e($accountStatus); ?></td>
        <td><?php echo e($member['created_at']); ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
  <?php if ($totalPages > 1): ?>
    <?php
      $firstVisiblePage = max(1, $page - 2);
      $lastVisiblePage = min($totalPages, $page + 2);
    ?>
    <nav class="admin-pagination" aria-label="Organization member pages">
      <?php if ($page > 1): ?>
        <a href="organization_members.php?id=<?php echo (int) $organization['id']; ?>&amp;page=<?php echo $page - 1; ?>" aria-label="Previous page">Previous</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Previous</span>
      <?php endif; ?>

      <?php if ($firstVisiblePage > 1): ?>
        <a href="organization_members.php?id=<?php echo (int) $organization['id']; ?>&amp;page=1">1</a>
        <?php if ($firstVisiblePage > 2): ?><span aria-hidden="true">…</span><?php endif; ?>
      <?php endif; ?>

      <?php for ($visiblePage = $firstVisiblePage; $visiblePage <= $lastVisiblePage; $visiblePage++): ?>
        <?php if ($visiblePage === $page): ?>
          <span class="current" aria-current="page"><?php echo $visiblePage; ?></span>
        <?php else: ?>
          <a href="organization_members.php?id=<?php echo (int) $organization['id']; ?>&amp;page=<?php echo $visiblePage; ?>"><?php echo $visiblePage; ?></a>
        <?php endif; ?>
      <?php endfor; ?>

      <?php if ($lastVisiblePage < $totalPages): ?>
        <?php if ($lastVisiblePage < $totalPages - 1): ?><span aria-hidden="true">…</span><?php endif; ?>
        <a href="organization_members.php?id=<?php echo (int) $organization['id']; ?>&amp;page=<?php echo $totalPages; ?>"><?php echo $totalPages; ?></a>
      <?php endif; ?>

      <?php if ($page < $totalPages): ?>
        <a href="organization_members.php?id=<?php echo (int) $organization['id']; ?>&amp;page=<?php echo $page + 1; ?>" aria-label="Next page">Next</a>
      <?php else: ?>
        <span class="disabled" aria-disabled="true">Next</span>
      <?php endif; ?>
    </nav>
  <?php endif; ?>
  <p><a href="organizations.php">Back to Organizations</a></p>
</div>
<?php admin_footer();
