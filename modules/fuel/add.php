<?php
/**
 * Add fuel log form.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('fuel');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid CSRF token.');
        redirect('modules/fuel/add.php');
    }

    $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
    $liters = (float)($_POST['quantity_liters'] ?? 0);
    $cost = (float)($_POST['cost'] ?? 0);
    $date = trim($_POST['logged_date'] ?? '');

    if ($vehicleId === 0 || $liters <= 0 || $cost <= 0 || $date === '') {
        setFlash('danger', 'All fuel details are required.');
        redirect('modules/fuel/add.php');
    }

    $insert = getDb()->prepare('INSERT INTO fuel_logs (vehicle_id, quantity_liters, cost, logged_date) VALUES (?, ?, ?, ?)');
    $insert->execute([$vehicleId, $liters, $cost, $date]);
    setFlash('success', 'Fuel log saved.');
    redirect('modules/fuel/index.php');
}

$vehicles = getDb()->query('SELECT id, registration_number FROM vehicles ORDER BY registration_number')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Log Fuel</h1>
    <p class="page-subtitle">Record refueling costs and fuel usage for each vehicle.</p>
    <div class="card dashboard-card">
        <form method="post" action="add.php" data-validate="true">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
            <div class="form-grid">
                <div class="form-group"><label>Vehicle</label><select name="vehicle_id" required>
                    <?php foreach ($vehicles as $vehicle): ?><option value="<?php echo e((string)$vehicle['id']); ?>"><?php echo e($vehicle['registration_number']); ?></option><?php endforeach; ?>
                </select></div>
                <div class="form-group"><label>Quantity (Liters)</label><input type="number" step="0.01" name="quantity_liters" required></div>
                <div class="form-group"><label>Cost</label><input type="number" step="0.01" name="cost" required></div>
                <div class="form-group"><label>Logged Date</label><input type="date" name="logged_date" required></div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save Fuel Log</button>
                <a class="btn btn-secondary" href="index.php">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
