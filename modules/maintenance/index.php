<?php
/**
 * Maintenance module index.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('maintenance');

$stmt = getDb()->query('SELECT m.id, v.registration_number, m.description, m.cost, m.status, m.scheduled_date FROM maintenance_logs m JOIN vehicles v ON v.id = m.vehicle_id ORDER BY m.id DESC');
$maintenance = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-section d-flex justify-between align-center">
        <div>
            <h1 class="page-title">Maintenance</h1>
            <p class="page-subtitle">Schedule inspections and upkeep for the fleet.</p>
        </div>
        <a class="btn btn-primary" href="add.php">Log Maintenance</a>
    </div>

    <div class="card table-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Vehicle</th>
                        <th>Description</th>
                        <th>Cost</th>
                        <th>Status</th>
                        <th>Scheduled</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($maintenance as $entry): ?>
                    <tr>
                        <td><?php echo e($entry['registration_number']); ?></td>
                        <td><?php echo e($entry['description']); ?></td>
                        <td><?php echo e(formatCurrency((float)$entry['cost'])); ?></td>
                        <td><span class="badge badge-warning"><?php echo e($entry['status']); ?></span></td>
                        <td><?php echo e(formatDate($entry['scheduled_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
