<?php
/**
 * Main dashboard page.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
requireAuth();

enforceModuleAccess('dashboard');

$stmt = getDb()->query('SELECT * FROM vw_dashboard_summary');
$summary = $stmt->fetch();

$vehicleStmt = getDb()->query('SELECT COUNT(*) AS total FROM vehicles');
$vehicleCount = $vehicleStmt->fetch();

$tripStmt = getDb()->query('SELECT COUNT(*) AS total FROM trips');
$tripCount = $tripStmt->fetch();

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>
    <section class="page-heading">
        <div><h1 class="page-title">Fleet performance overview</h1><p class="page-subtitle">Monitor vehicles, drivers, trips and operational health in real time.</p></div>
        <a class="btn btn-primary" href="modules/trips/add.php"><i class="fa fa-plus" aria-hidden="true"></i> Create trip</a>
    </section>

    <div class="dashboard-grid">
        <div class="card stat-card">
            <div><div class="label">Active Vehicles</div><div class="value"><?php echo e((string)($summary['active_vehicles'] ?? 0)); ?></div><div class="trend"><i class="fa fa-arrow-up"></i> Fleet online</div></div>
            <div class="icon"><i class="fa fa-bus"></i></div>
        </div>
        <div class="card stat-card">
            <div><div class="label">Available Drivers</div><div class="value"><?php echo e((string)($summary['drivers_available'] ?? 0)); ?></div><div class="trend"><i class="fa fa-check"></i> Ready to assign</div></div>
            <div class="icon"><i class="fa fa-user"></i></div>
        </div>
        <div class="card stat-card">
            <div><div class="label">Trips Running</div><div class="value"><?php echo e((string)($summary['trips_running'] ?? 0)); ?></div><div class="trend"><i class="fa fa-location-arrow"></i> Live operations</div></div>
            <div class="icon"><i class="fa fa-road"></i></div>
        </div>
        <div class="card stat-card">
            <div><div class="label">Total Vehicles</div><div class="value"><?php echo e((string)($vehicleCount['total'] ?? 0)); ?></div><div class="trend"><i class="fa fa-line-chart"></i> Managed assets</div></div>
            <div class="icon"><i class="fa fa-chart-pie"></i></div>
        </div>
    </div>

    <div class="grid grid-2 page-section">
        <div class="card chart-card">
            <h3>Monthly trips</h3>
            <canvas id="dashboard-chart" height="220"></canvas>
        </div>
        <div class="card dashboard-card">
            <h3>Operational snapshot</h3>
            <ul class="snapshot-list">
                <li><span>Vehicles in maintenance</span><strong><?php echo e((string)($summary['vehicles_in_maintenance'] ?? 0)); ?></strong></li>
                <li><span>Drivers on trip</span><strong><?php echo e((string)($summary['drivers_on_trip'] ?? 0)); ?></strong></li>
                <li><span>Trips completed</span><strong><?php echo e((string)($summary['trips_completed'] ?? 0)); ?></strong></li>
                <li><span>Trips cancelled</span><strong><?php echo e((string)($summary['trips_cancelled'] ?? 0)); ?></strong></li>
            </ul>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
