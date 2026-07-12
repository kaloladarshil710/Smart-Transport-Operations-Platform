<?php
/**
 * Notifications module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('notifications');

$stmt = getDb()->prepare('SELECT id, title, message, is_read, created_at FROM notifications WHERE user_id = ? ORDER BY id DESC');
$stmt->execute([currentUser()['id']]);
$notifications = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Notifications</h1>
    <p class="page-subtitle">Track alerts, reminders and operational messages.</p>
    <div class="card table-card">
        <div class="table-responsive">
            <table>
                <thead><tr><th>Title</th><th>Message</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($notifications as $notification): ?>
                    <tr>
                        <td><?php echo e($notification['title']); ?></td>
                        <td><?php echo e($notification['message']); ?></td>
                        <td><?php echo $notification['is_read'] ? '<span class="badge badge-success">Read</span>' : '<span class="badge badge-warning">New</span>'; ?></td>
                        <td><?php echo e(formatDate($notification['created_at'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
