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
    <h1 class="page-title">Fleet performance overview</h1>
    <p class="page-subtitle">Monitor vehicles, drivers, trips and operational health in real time.</p>

    <div class="dashboard-grid">
        <div class="card stat-card">
            <div><div class="label">Active Vehicles</div><div class="value"><?php echo e((string)($summary['active_vehicles'] ?? 0)); ?></div></div>
            <div class="icon"><i class="fa fa-bus"></i></div>
        </div>
        <div class="card stat-card">
            <div><div class="label">Available Drivers</div><div class="value"><?php echo e((string)($summary['drivers_available'] ?? 0)); ?></div></div>
            <div class="icon"><i class="fa fa-user"></i></div>
        </div>
        <div class="card stat-card">
            <div><div class="label">Trips Running</div><div class="value"><?php echo e((string)($summary['trips_running'] ?? 0)); ?></div></div>
            <div class="icon"><i class="fa fa-road"></i></div>
        </div>
        <div class="card stat-card">
            <div><div class="label">Total Vehicles</div><div class="value"><?php echo e((string)($vehicleCount['total'] ?? 0)); ?></div></div>
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
            <ul>
                <li>Vehicles in maintenance: <?php echo e((string)($summary['vehicles_in_maintenance'] ?? 0)); ?></li>
                <li>Drivers on trip: <?php echo e((string)($summary['drivers_on_trip'] ?? 0)); ?></li>
                <li>Trips completed: <?php echo e((string)($summary['trips_completed'] ?? 0)); ?></li>
                <li>Trips cancelled: <?php echo e((string)($summary['trips_cancelled'] ?? 0)); ?></li>
            </ul>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
