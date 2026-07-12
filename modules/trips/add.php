<?php
/**
 * Create trip form.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('trips');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid CSRF token.');
        redirect('modules/trips/add.php');
    }

    $tripNumber = trim($_POST['trip_number'] ?? '');
    $vehicleId = (int)($_POST['vehicle_id'] ?? 0);
    $driverId = (int)($_POST['driver_id'] ?? 0);
    $cargoWeight = (int)($_POST['cargo_weight_kg'] ?? 0);
    $origin = trim($_POST['origin'] ?? '');
    $destination = trim($_POST['destination'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $revenue = (float)($_POST['revenue'] ?? 0);

    if ($tripNumber === '' || $vehicleId === 0 || $driverId === 0 || $cargoWeight === 0 || $origin === '' || $destination === '' || $startDate === '') {
        setFlash('danger', 'All required fields must be filled.');
        redirect('modules/trips/add.php');
    }

    $vehicle = getDb()->prepare('SELECT id, capacity_kg, status FROM vehicles WHERE id = ? LIMIT 1');
    $vehicle->execute([$vehicleId]);
    $vehicleRow = $vehicle->fetch();

    if (!$vehicleRow) {
        setFlash('danger', 'Vehicle not found.');
        redirect('modules/trips/add.php');
    }

    if ($vehicleRow['status'] !== 'Active') {
        setFlash('danger', 'Vehicle is not available for trip assignment.');
        redirect('modules/trips/add.php');
    }

    $driver = getDb()->prepare('SELECT id, status, license_expiry FROM drivers WHERE id = ? LIMIT 1');
    $driver->execute([$driverId]);
    $driverRow = $driver->fetch();

    if (!$driverRow) {
        setFlash('danger', 'Driver not found.');
        redirect('modules/trips/add.php');
    }

    if ($cargoWeight > (int)$vehicleRow['capacity_kg']) {
        setFlash('danger', 'Cargo weight exceeds vehicle capacity.');
        redirect('modules/trips/add.php');
    }

    if ($driverRow['status'] !== 'Available') {
        setFlash('danger', 'Driver is not available for assignment.');
        redirect('modules/trips/add.php');
    }

    $insert = getDb()->prepare('INSERT INTO trips (trip_number, vehicle_id, driver_id, cargo_weight_kg, origin, destination, start_date, status, revenue) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insert->execute([$tripNumber, $vehicleId, $driverId, $cargoWeight, $origin, $destination, $startDate, 'Scheduled', $revenue]);

    getDb()->prepare('UPDATE vehicles SET status = ? WHERE id = ?')->execute([VEHICLE_STATUS_ON_TRIP, $vehicleId]);
    getDb()->prepare('UPDATE drivers SET status = ? WHERE id = ?')->execute([DRIVER_STATUS_ON_TRIP, $driverId]);

    setFlash('success', 'Trip created successfully.');
    redirect('modules/trips/index.php');
}

$vehicles = getDb()->query('SELECT id, registration_number FROM vehicles WHERE status = "Active" ORDER BY registration_number')->fetchAll();
$drivers = getDb()->query('SELECT id, full_name FROM drivers WHERE status = "Available" ORDER BY full_name')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Create Trip</h1>
    <p class="page-subtitle">Dispatch a vehicle and driver with route and revenue details.</p>
    <div class="card dashboard-card">
        <form method="post" action="add.php" data-validate="true">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
            <div class="form-grid">
                <div class="form-group"><label>Trip Number</label><input name="trip_number" required></div>
                <div class="form-group"><label>Vehicle</label><select name="vehicle_id" required>
                    <?php foreach ($vehicles as $vehicle): ?><option value="<?php echo e((string)$vehicle['id']); ?>"><?php echo e($vehicle['registration_number']); ?></option><?php endforeach; ?>
                </select></div>
                <div class="form-group"><label>Driver</label><select name="driver_id" required>
                    <?php foreach ($drivers as $driver): ?><option value="<?php echo e((string)$driver['id']); ?>"><?php echo e($driver['full_name']); ?></option><?php endforeach; ?>
                </select></div>
                <div class="form-group"><label>Cargo Weight (kg)</label><input type="number" name="cargo_weight_kg" required></div>
                <div class="form-group"><label>Origin</label><input name="origin" required></div>
                <div class="form-group"><label>Destination</label><input name="destination" required></div>
                <div class="form-group"><label>Start Date</label><input type="date" name="start_date" required></div>
                <div class="form-group"><label>Revenue</label><input type="number" step="0.01" name="revenue" required></div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Dispatch Trip</button>
                <a class="btn btn-secondary" href="index.php">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
