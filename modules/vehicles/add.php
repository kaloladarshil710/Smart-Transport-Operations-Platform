<?php
/**
 * Add vehicle form.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('vehicles');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid CSRF token.');
        redirect('modules/vehicles/add.php');
    }

    $registration = trim($_POST['registration_number'] ?? '');
    $make = trim($_POST['make'] ?? '');
    $model = trim($_POST['model'] ?? '');
    $year = (int)($_POST['year'] ?? 0);
    $type = (int)($_POST['vehicle_type_id'] ?? 0);
    $capacity = (int)($_POST['capacity_kg'] ?? 0);
    $region = (int)($_POST['region_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'Available');

    if ($registration === '' || $make === '' || $model === '' || $year === 0 || $type === 0 || $capacity === 0 || $region === 0) {
        setFlash('danger', 'All fields are required.');
        redirect('modules/vehicles/add.php');
    }

    $stmt = getDb()->prepare('SELECT id FROM vehicles WHERE registration_number = ? LIMIT 1');
    $stmt->execute([$registration]);
    if ($stmt->fetch()) {
        setFlash('danger', 'Vehicle registration number already exists.');
        redirect('modules/vehicles/add.php');
    }

    $insert = getDb()->prepare('INSERT INTO vehicles (registration_number, make, model, year, vehicle_type_id, capacity_kg, status, region_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
    $insert->execute([$registration, $make, $model, $year, $type, $capacity, $status, $region]);
    setFlash('success', 'Vehicle created successfully.');
    redirect('modules/vehicles/index.php');
}

$types = getDb()->query('SELECT id, name FROM vehicle_types ORDER BY name')->fetchAll();
$regions = getDb()->query('SELECT id, name FROM regions ORDER BY name')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Add Vehicle</h1>
    <p class="page-subtitle">Create a new fleet asset and assign operational metadata.</p>
    <div class="card dashboard-card">
        <form method="post" action="add.php" data-validate="true">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
            <div class="form-grid">
                <div class="form-group"><label>Registration Number</label><input name="registration_number" required></div>
                <div class="form-group"><label>Make</label><input name="make" required></div>
                <div class="form-group"><label>Model</label><input name="model" required></div>
                <div class="form-group"><label>Year</label><input type="number" name="year" required></div>
                <div class="form-group"><label>Vehicle Type</label><select name="vehicle_type_id" required>
                    <?php foreach ($types as $type): ?><option value="<?php echo e((string)$type['id']); ?>"><?php echo e($type['name']); ?></option><?php endforeach; ?>
                </select></div>
                <div class="form-group"><label>Capacity (kg)</label><input type="number" name="capacity_kg" required></div>
                <div class="form-group"><label>Region</label><select name="region_id" required>
                    <?php foreach ($regions as $region): ?><option value="<?php echo e((string)$region['id']); ?>"><?php echo e($region['name']); ?></option><?php endforeach; ?>
                </select></div>
                <div class="form-group"><label>Status</label><select name="status"><option>Available</option><option>In Shop</option><option>Retired</option></select></div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save Vehicle</button>
                <a class="btn btn-secondary" href="index.php">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
