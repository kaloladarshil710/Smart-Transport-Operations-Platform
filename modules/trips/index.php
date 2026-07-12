<?php
/**
 * Trip module index.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('trips');

$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$stmt = getDb()->prepare('SELECT t.id, t.trip_number, v.registration_number, d.full_name, t.origin, t.destination, t.status FROM trips t JOIN vehicles v ON v.id = t.vehicle_id JOIN drivers d ON d.id = t.driver_id ORDER BY t.id DESC LIMIT ? OFFSET ?');
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$trips = $stmt->fetchAll();

$countStmt = getDb()->query('SELECT COUNT(*) AS total FROM trips');
$count = $countStmt->fetch();
$totalPages = (int)ceil((int)$count['total'] / $limit);

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-section d-flex justify-between align-center">
        <div>
            <h1 class="page-title">Trip Management</h1>
            <p class="page-subtitle">Plan and monitor dispatches across the fleet.</p>
        </div>
        <a class="btn btn-primary" href="add.php">Create Trip</a>
    </div>

    <div class="card table-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Trip No.</th>
                        <th>Vehicle</th>
                        <th>Driver</th>
                        <th>Route</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($trips as $trip): ?>
                    <tr>
                        <td><?php echo e($trip['trip_number']); ?></td>
                        <td><?php echo e($trip['registration_number']); ?></td>
                        <td><?php echo e($trip['full_name']); ?></td>
                        <td><?php echo e($trip['origin'] . ' → ' . $trip['destination']); ?></td>
                        <td><span class="badge badge-warning"><?php echo e($trip['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="mt-3">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a class="btn btn-secondary" href="index.php?page=<?php echo e((string)$i); ?>"><?php echo e((string)$i); ?></a>
            <?php endfor; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
