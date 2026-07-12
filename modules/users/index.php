<?php
/**
 * Users module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('users');

$stmt = getDb()->query('SELECT id, full_name, email, role, status FROM users ORDER BY id DESC');
$users = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Users</h1>
    <p class="page-subtitle">Manage internal users and access roles.</p>
    <div class="card table-card">
        <div class="table-responsive">
            <table>
                <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo e($user['full_name']); ?></td>
                        <td><?php echo e($user['email']); ?></td>
                        <td><?php echo e($user['role']); ?></td>
                        <td><span class="badge badge-success"><?php echo e($user['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
