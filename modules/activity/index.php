<?php
/**
 * Activity logs module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();

$stmt = getDb()->query('SELECT id, action, description, created_at FROM activity_logs ORDER BY id DESC LIMIT 20');
$logs = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Activity Logs</h1>
    <p class="page-subtitle">Audit user actions and operational events.</p>
    <div class="card table-card">
        <div class="table-responsive">
            <table>
                <thead><tr><th>Action</th><th>Description</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><?php echo e($log['action']); ?></td>
                        <td><?php echo e($log['description']); ?></td>
                        <td><?php echo e(formatDate($log['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
