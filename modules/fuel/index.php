<?php
/**
 * Fuel module index.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('fuel');

$stmt = getDb()->query('SELECT f.id, v.registration_number, f.quantity_liters, f.cost, f.logged_date FROM fuel_logs f JOIN vehicles v ON v.id = f.vehicle_id ORDER BY f.id DESC');
$fuel = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-section d-flex justify-between align-center">
        <div>
            <h1 class="page-title">Fuel Logs</h1>
            <p class="page-subtitle">Monitor fuel consumption and operating expenses.</p>
        </div>
        <a class="btn btn-primary" href="add.php">Log Fuel</a>
    </div>
    <div class="card table-card">
        <div class="table-responsive">
            <table>
                <thead><tr><th>Vehicle</th><th>Liters</th><th>Cost</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($fuel as $entry): ?>
                    <tr>
                        <td><?php echo e($entry['registration_number']); ?></td>
                        <td><?php echo e((string)$entry['quantity_liters']); ?></td>
                        <td><?php echo e(formatCurrency((float)$entry['cost'])); ?></td>
                        <td><?php echo e(formatDate($entry['logged_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
