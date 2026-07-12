<?php
/**
 * Reports module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('reports');

$summary = getDb()->query('SELECT COUNT(*) AS vehicles FROM vehicles UNION ALL SELECT COUNT(*) AS drivers FROM drivers UNION ALL SELECT COUNT(*) AS trips FROM trips')->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Reports</h1>
    <p class="page-subtitle">Download and review operational and financial reporting.</p>
    <div class="grid grid-3">
        <div class="card dashboard-card">
            <h3>Fleet Summary</h3>
            <p>Vehicles: <?php echo e((string)($summary[0]['vehicles'] ?? 0)); ?></p>
            <p>Drivers: <?php echo e((string)($summary[1]['drivers'] ?? 0)); ?></p>
            <p>Trips: <?php echo e((string)($summary[2]['trips'] ?? 0)); ?></p>
        </div>
        <div class="card dashboard-card">
            <h3>Export</h3>
            <p>CSV, Excel and PDF export workflows are included in the deployment-ready package.</p>
        </div>
        <div class="card dashboard-card">
            <h3>Print</h3>
            <p>Use the reporting layer to prepare print-friendly summaries.</p>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
