<?php
/**
 * Add maintenance form.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('maintenance');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid CSRF token.');
        redirect('modules/maintenance/add.php');
    }

    $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $cost = (float)($_POST['cost'] ?? 0);
    $scheduledDate = trim($_POST['scheduled_date'] ?? '');

    if ($vehicleId === 0 || $description === '' || $cost < 0 || $scheduledDate === '') {
        setFlash('danger', 'Maintenance details are required.');
        redirect('modules/maintenance/add.php');
    }

    $insert = getDb()->prepare('INSERT INTO maintenance_logs (vehicle_id, description, cost, status, scheduled_date) VALUES (?, ?, ?, ?, ?)');
    $insert->execute([$vehicleId, $description, $cost, 'Pending', $scheduledDate]);
    getDb()->prepare('UPDATE vehicles SET status = ? WHERE id = ?')->execute([VEHICLE_STATUS_IN_SHOP, $vehicleId]);

    setFlash('success', 'Maintenance entry created.');
    redirect('modules/maintenance/index.php');
}

$vehicles = getDb()->query('SELECT id, registration_number FROM vehicles ORDER BY registration_number')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Log Maintenance</h1>
    <p class="page-subtitle">Track repairs and service status for your fleet.</p>
    <div class="card dashboard-card">
        <form method="post" action="add.php" data-validate="true">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
            <div class="form-grid">
                <div class="form-group"><label>Vehicle</label><select name="vehicle_id" required>
                    <?php foreach ($vehicles as $vehicle): ?><option value="<?php echo e((string)$vehicle['id']); ?>"><?php echo e($vehicle['registration_number']); ?></option><?php endforeach; ?>
                </select></div>
                <div class="form-group"><label>Cost</label><input type="number" step="0.01" name="cost" required></div>
                <div class="form-group"><label>Scheduled Date</label><input type="date" name="scheduled_date" required></div>
                <div class="form-group"><label>Description</label><textarea name="description" required></textarea></div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save Maintenance</button>
                <a class="btn btn-secondary" href="index.php">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
