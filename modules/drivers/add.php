<?php
/**
 * Add driver form.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('drivers');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid CSRF token.');
        redirect('modules/drivers/add.php');
    }

    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $license = trim($_POST['license_number'] ?? '');
    $expiry = trim($_POST['license_expiry'] ?? '');
    $region = (int)($_POST['region_id'] ?? 0);
    $status = trim($_POST['status'] ?? 'Available');

    if ($name === '' || $email === '' || $phone === '' || $license === '' || $expiry === '' || $region === 0) {
        setFlash('danger', 'All fields are required.');
        redirect('modules/drivers/add.php');
    }

    $stmt = getDb()->prepare('SELECT id FROM drivers WHERE email = ? OR license_number = ? LIMIT 1');
    $stmt->execute([$email, $license]);
    if ($stmt->fetch()) {
        setFlash('danger', 'Driver email or license number already exists.');
        redirect('modules/drivers/add.php');
    }

    $insert = getDb()->prepare('INSERT INTO drivers (full_name, email, phone, license_number, license_expiry, status, region_id) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $insert->execute([$name, $email, $phone, $license, $expiry, $status, $region]);
    setFlash('success', 'Driver created successfully.');
    redirect('modules/drivers/index.php');
}

$regions = getDb()->query('SELECT id, name FROM regions ORDER BY name')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Add Driver</h1>
    <p class="page-subtitle">Register a licensed driver and assign operating region.</p>
    <div class="card dashboard-card">
        <form method="post" action="add.php" data-validate="true">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
            <div class="form-grid">
                <div class="form-group"><label>Full Name</label><input name="full_name" required></div>
                <div class="form-group"><label>Email</label><input type="email" name="email" required></div>
                <div class="form-group"><label>Phone</label><input name="phone" required></div>
                <div class="form-group"><label>License Number</label><input name="license_number" required></div>
                <div class="form-group"><label>License Expiry</label><input type="date" name="license_expiry" required></div>
                <div class="form-group"><label>Region</label><select name="region_id" required>
                    <?php foreach ($regions as $region): ?><option value="<?php echo e((string)$region['id']); ?>"><?php echo e($region['name']); ?></option><?php endforeach; ?>
                </select></div>
                <div class="form-group"><label>Status</label><select name="status"><option>Available</option><option>On Trip</option><option>Suspended</option></select></div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save Driver</button>
                <a class="btn btn-secondary" href="index.php">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
