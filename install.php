<?php
/**
 * One-time installer. Open this file in the browser after starting Apache + MySQL.
 * It creates the omds database, tables, upload folders, and the first admin account.
 */
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/config/db.php';

start_omds_session();

$lockFile = __DIR__ . '/install.lock';
$schemaFile = __DIR__ . '/sql/schema.sql';
$message = '';
$error = '';
$created = false;

$defaultAdmin = [
    'username' => 'omds_admin',
    'gmail' => 'omds.admin@gmail.com',
    'password' => 'AdminOMDS!2026',
    'public_user_id' => '1000000001',
];

function run_sql_file(PDO $pdo, string $sql): void
{
    $sql = preg_replace('/^\s*--.*$/m', '', $sql);
    $parts = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($parts as $part) {
        if ($part === '') {
            continue;
        }
        $pdo->exec($part);
    }
}

if (is_file($lockFile) && empty($_GET['force'])) {
    $message = 'OMDS is already installed. Delete install.lock only if you need to run setup again.';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $root = dbWithoutDatabase();
        $schema = file_get_contents($schemaFile);
        if ($schema === false) {
            throw new RuntimeException('Could not read sql/schema.sql');
        }
        run_sql_file($root, $schema);

        $pdo = db();
        $check = $pdo->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1")->fetch();
        if (!$check) {
            $hash = password_hash($defaultAdmin['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'INSERT INTO users (public_user_id, username, gmail, password_hash, first_name, last_name, role, account_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $defaultAdmin['public_user_id'],
                $defaultAdmin['username'],
                $defaultAdmin['gmail'],
                $hash,
                'System',
                'Administrator',
                'admin',
                'active',
            ]);
            $adminId = (int) $pdo->lastInsertId();
            $pdo->prepare('INSERT INTO user_profiles (user_id) VALUES (?)')->execute([$adminId]);
            audit_log($pdo, $adminId, 'admin_seeded', 'user', (string) $adminId, 'Initial administrator account created');
        }

        $folders = [
            __DIR__ . '/uploads',
            __DIR__ . '/uploads/personnel_ids',
            __DIR__ . '/uploads/medical',
            __DIR__ . '/uploads/profiles',
        ];
        foreach ($folders as $folder) {
            if (!is_dir($folder)) {
                mkdir($folder, 0755, true);
            }
        }

        file_put_contents($lockFile, date('c'));
        $created = true;
        $message = 'Installation finished.';
    } catch (Throwable $e) {
        $error = 'Installation failed. Check that MySQL is running and that config/db.php has the correct password. (' . $e->getMessage() . ')';
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OMDS Install</title>
    <link rel="stylesheet" href="css/signup.css">
</head>
<body>
<div class="main-area">
    <header class="page-header">
        <h1>OMDS Setup</h1>
    </header>
    <div class="signup-container">
        <div class="signup-card">
            <h2>Install database</h2>
            <?php if ($error): ?>
                <p class="form-error"><?php echo e($error); ?></p>
            <?php endif; ?>
            <?php if ($message): ?>
                <p class="<?php echo $created ? 'form-success' : 'form-note'; ?>"><?php echo e($message); ?></p>
            <?php endif; ?>
            <?php if ($created): ?>
                <p><strong>Admin username:</strong> <?php echo e($defaultAdmin['username']); ?></p>
                <p><strong>Admin account ID:</strong> <?php echo e($defaultAdmin['public_user_id']); ?></p>
                <p><strong>Admin password:</strong> <?php echo e($defaultAdmin['password']); ?></p>
                <p class="form-note">Change this password after you log in. Do not leave this page public on a shared computer.</p>
                <p><a href="login.php">Go to login</a></p>
            <?php elseif (!is_file($lockFile)): ?>
                <p>This creates the <code>omds</code> MySQL database and the first administrator. Visitors cannot choose the admin role on the public signup page.</p>
                <form method="post">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="submit-btn">Install OMDS</button>
                </form>
            <?php else: ?>
                <p><a href="login.php">Go to login</a></p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
