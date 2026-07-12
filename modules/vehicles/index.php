<?php

/**
 * Vehicle module index.
 */

declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('vehicles');

$limit = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;

$stmt = getDb()->prepare(
    'SELECT v.id, v.registration_number, v.make, v.model, v.status, vt.name AS vehicle_type, r.name AS region FROM vehicles v JOIN vehicle_types vt ON vt.id = v.vehicle_type_id JOIN regions r ON r.id = v.region_id WHERE v.deleted_at IS NULL ORDER BY v.id DESC LIMIT ? OFFSET ?'
);
$stmt->bindValue(1, $limit, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$vehicles = $stmt->fetchAll();

$countStmt = getDb()->query('SELECT COUNT(*) AS total FROM vehicles WHERE deleted_at IS NULL');
$count = $countStmt->fetch();
$totalPages = (int)ceil((int)$count['total'] / $limit);

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-section d-flex justify-between align-center">
        <div>
            <h1 class="page-title">Vehicle Management</h1>
            <p class="page-subtitle">Track fleet assets, lifecycle state and utilization.</p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline" href="<?= e(siteUrl('ajax/vehicle_export.php')); ?>">CSV export</a>
            <a class="btn btn-primary" href="add.php">Add Vehicle</a>
        </div>
    </div>

   <div class="card table-card">
    <div class="table-responsive">
        <table>
            <thead>
                <tr>
                    <th>Registration</th>
                    <th>Make / Model</th>
                    <th>Type</th>
                    <th>Region</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($vehicles as $vehicle): ?>
                <tr>
                    <td>
                        <a class="trip-link" href="view.php?id=<?php echo (int)$vehicle['id']; ?>">
                            <?php echo e($vehicle['registration_number']); ?>
                        </a>
                    </td>

                    <td>
                        <?php echo e($vehicle['make'] . ' ' . $vehicle['model']); ?>
                    </td>

                    <td>
                        <?php echo e($vehicle['vehicle_type']); ?>
                    </td>

                    <td>
                        <?php echo e($vehicle['region']); ?>
                    </td>

                    <td>
                        <?php
                        $statusClass = 'badge-success';

                        switch (strtolower($vehicle['status'])) {
                            case 'maintenance':
                                $statusClass = 'badge-warning';
                                break;
                            case 'inactive':
                                $statusClass = 'badge-danger';
                                break;
                            case 'available':
                            default:
                                $statusClass = 'badge-success';
                                break;
                        }
                        ?>
                        <span class="badge <?php echo $statusClass; ?>">
                            <?php echo e($vehicle['status']); ?>
                        </span>
                    </td>

                    <td>
                        <a class="btn btn-sm btn-primary"
                           href="view.php?id=<?php echo (int)$vehicle['id']; ?>">
                            <i class="fa fa-eye">View</i> 
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>

                <?php if (!$vehicles): ?>
                <tr>
                    <td colspan="6" class="empty-state">
                        No fleet vehicles yet. Add your first vehicle to begin dispatch operations.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="mt-3">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a class="btn btn-secondary"
               href="index.php?page=<?php echo $i; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>
    </div>
</div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
