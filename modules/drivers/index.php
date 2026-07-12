<?php
/**
 * Driver module index.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('drivers');

$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$stmt = getDb()->prepare('SELECT d.id, d.full_name, d.email, d.phone, d.status, d.license_number, r.name AS region FROM drivers d JOIN regions r ON r.id = d.region_id ORDER BY d.id DESC LIMIT ? OFFSET ?');
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$drivers = $stmt->fetchAll();

$countStmt = getDb()->query('SELECT COUNT(*) AS total FROM drivers');
$count = $countStmt->fetch();
$totalPages = (int)ceil((int)$count['total'] / $limit);

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-section d-flex justify-between align-center">
        <div>
            <h1 class="page-title">Driver Management</h1>
            <p class="page-subtitle">Coordinate licensed operators and their availability.</p>
        </div>
        <a class="btn btn-primary" href="add.php">Add Driver</a>
    </div>

    <div class="card table-card">
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>License</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($drivers as $driver): ?>
                    <tr>
                        <td><?php echo e($driver['full_name']); ?></td>
                        <td><?php echo e($driver['email']); ?></td>
                        <td><?php echo e($driver['phone']); ?></td>
                        <td><?php echo e($driver['license_number']); ?></td>
                        <td><span class="badge badge-info"><?php echo e($driver['status']); ?></span></td>
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
