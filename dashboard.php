<?php
/**
 * TransitOps Enterprise Dashboard
 *
 * Premium Fleet Management ERP Dashboard - First screen after login.
 * Features KPIs, analytics charts, vehicle/driver status, recent trips,
 * activities, maintenance alerts, license alerts, notifications, and more.
 *
 * @package TransitOps
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';
requireAuth();
enforceModuleAccess('dashboard');

require_once __DIR__ . '/functions/dashboard_functions.php';

$kpi = getKpiSummary();
$widgets = getDashboardWidgetsByRole();
$vehicleStatus = getVehicleStatusDistribution();
$driverStatus = getDriverStatusDistribution();
$recentTrips = getRecentTrips(10);
$activities = getRecentActivities(15);
$maintenanceAlerts = getMaintenanceAlerts();
$documentAlerts = getVehicleDocumentAlerts();
$licenseAlerts = getLicenseAlerts();
$unreadNotifications = getUnreadNotificationCount();
$notifications = getRecentNotifications(8);
$user = currentUser();

$pageTitle = 'Dashboard';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => 'dashboard.php'],
];

require_once __DIR__ . '/includes/header.php';
require_once __DIR__ . '/includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/includes/navbar.php'; ?>
    <?php require_once __DIR__ . '/includes/breadcrumb.php'; ?>

    <!-- Dashboard Header -->
    <section class="dashboard-header">
        <div class="dashboard-header-left">
            <h1 class="dashboard-title">Fleet Performance Dashboard</h1>
            <p class="dashboard-subtitle">Real-time overview of your entire transport operations.</p>
        </div>
        <div class="dashboard-header-right">
            <div class="dashboard-datetime">
                <span id="current-date" class="current-date"><?php echo e(date('l, d F Y')); ?></span>
                <span id="current-time" class="current-time"><?php echo e(date('H:i:s')); ?></span>
            </div>
            <div class="dashboard-welcome">
                <span class="welcome-text">Welcome back,</span>
                <span class="welcome-user"><?php echo e($user['full_name'] ?? 'User'); ?></span>
                <span class="welcome-role badge badge-role"><?php echo e(ucwords(str_replace('_', ' ', $user['role'] ?? 'operator'))); ?></span>
            </div>
            <div class="dashboard-actions">
                <button type="button" class="btn btn-primary" onclick="window.location.href='modules/vehicles/add.php'"><i class="fa fa-plus"></i> Add Vehicle</button>
                <button type="button" class="btn btn-secondary" onclick="window.location.href='modules/drivers/add.php'"><i class="fa fa-user-plus"></i> Add Driver</button>
                <button type="button" class="btn btn-accent" onclick="window.location.href='modules/trips/add.php'"><i class="fa fa-route"></i> Create Trip</button>
            </div>
        </div>
    </section>

    <!-- KPI Cards Section -->
    <?php if ($widgets['kpi_vehicles'] || $widgets['kpi_drivers'] || $widgets['kpi_trips'] || $widgets['kpi_finance']): ?>
    <section class="kpi-section page-section">
        <div class="kpi-grid">
            <!-- Total Vehicles -->
            <div class="kpi-card" data-kpi="total_vehicles">
                <div class="kpi-icon kpi-icon-vehicles"><i class="fa fa-bus"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Total Vehicles</span>
                    <span class="kpi-value" id="kpi-total-vehicles"><?php echo e((string)($kpi['total_vehicles'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-arrow-up"></i> Fleet total</span>
                </div>
                <div class="kpi-mini-chart" id="mini-total-vehicles"></div>
            </div>

            <!-- Available Vehicles -->
            <div class="kpi-card" data-kpi="available_vehicles">
                <div class="kpi-icon kpi-icon-available"><i class="fa fa-check-circle"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Available Vehicles</span>
                    <span class="kpi-value" id="kpi-available-vehicles"><?php echo e((string)($kpi['available_vehicles'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-arrow-up"></i> Ready to deploy</span>
                </div>
                <div class="kpi-mini-chart" id="mini-available-vehicles"></div>
            </div>

            <!-- Vehicles On Trip -->
            <div class="kpi-card" data-kpi="vehicles_on_trip">
                <div class="kpi-icon kpi-icon-trip"><i class="fa fa-route"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Vehicles On Trip</span>
                    <span class="kpi-value" id="kpi-vehicles-on-trip"><?php echo e((string)($kpi['vehicles_on_trip'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-info"><i class="fa fa-arrow-right"></i> In transit</span>
                </div>
                <div class="kpi-mini-chart" id="mini-vehicles-on-trip"></div>
            </div>

            <!-- Vehicles In Maintenance -->
            <div class="kpi-card" data-kpi="vehicles_in_maintenance">
                <div class="kpi-icon kpi-icon-maintenance"><i class="fa fa-wrench"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">In Maintenance</span>
                    <span class="kpi-value" id="kpi-maintenance"><?php echo e((string)($kpi['vehicles_in_maintenance'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-warning"><i class="fa fa-exclamation-triangle"></i> In shop</span>
                </div>
                <div class="kpi-mini-chart" id="mini-maintenance"></div>
            </div>

            <!-- Retired Vehicles -->
            <div class="kpi-card" data-kpi="retired_vehicles">
                <div class="kpi-icon kpi-icon-retired"><i class="fa fa-archive"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Retired Vehicles</span>
                    <span class="kpi-value" id="kpi-retired"><?php echo e((string)($kpi['retired_vehicles'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-muted"><i class="fa fa-minus"></i> Decommissioned</span>
                </div>
                <div class="kpi-mini-chart" id="mini-retired"></div>
            </div>

            <!-- Total Drivers -->
            <div class="kpi-card" data-kpi="total_drivers">
                <div class="kpi-icon kpi-icon-drivers"><i class="fa fa-users"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Total Drivers</span>
                    <span class="kpi-value" id="kpi-total-drivers"><?php echo e((string)($kpi['total_drivers'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-arrow-up"></i> Workforce</span>
                </div>
                <div class="kpi-mini-chart" id="mini-total-drivers"></div>
            </div>

            <!-- Available Drivers -->
            <div class="kpi-card" data-kpi="available_drivers">
                <div class="kpi-icon kpi-icon-avail-drivers"><i class="fa fa-user-check"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Available Drivers</span>
                    <span class="kpi-value" id="kpi-available-drivers"><?php echo e((string)($kpi['available_drivers'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-arrow-up"></i> Ready</span>
                </div>
                <div class="kpi-mini-chart" id="mini-available-drivers"></div>
            </div>

            <!-- Drivers On Trip -->
            <div class="kpi-card" data-kpi="drivers_on_trip">
                <div class="kpi-icon kpi-icon-drivers-trip"><i class="fa fa-user-tie"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Drivers On Trip</span>
                    <span class="kpi-value" id="kpi-drivers-trip"><?php echo e((string)($kpi['drivers_on_trip'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-info"><i class="fa fa-arrow-right"></i> Driving</span>
                </div>
                <div class="kpi-mini-chart" id="mini-drivers-trip"></div>
            </div>

            <!-- Suspended Drivers -->
            <div class="kpi-card" data-kpi="suspended_drivers">
                <div class="kpi-icon kpi-icon-suspended"><i class="fa fa-ban"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Suspended Drivers</span>
                    <span class="kpi-value" id="kpi-suspended"><?php echo e((string)($kpi['suspended_drivers'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-down"><i class="fa fa-arrow-down"></i> Inactive</span>
                </div>
                <div class="kpi-mini-chart" id="mini-suspended"></div>
            </div>

            <!-- Trips Today -->
            <div class="kpi-card" data-kpi="trips_today">
                <div class="kpi-icon kpi-icon-trips"><i class="fa fa-calendar-day"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Trips Today</span>
                    <span class="kpi-value" id="kpi-trips-today"><?php echo e((string)($kpi['trips_today'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-arrow-up"></i> New</span>
                </div>
                <div class="kpi-mini-chart" id="mini-trips-today"></div>
            </div>

            <!-- Active Trips -->
            <div class="kpi-card" data-kpi="active_trips">
                <div class="kpi-icon kpi-icon-active-trips"><i class="fa fa-spinner"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Active Trips</span>
                    <span class="kpi-value" id="kpi-active-trips"><?php echo e((string)($kpi['active_trips'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-info"><i class="fa fa-play"></i> In progress</span>
                </div>
                <div class="kpi-mini-chart" id="mini-active-trips"></div>
            </div>

            <!-- Completed Trips -->
            <div class="kpi-card" data-kpi="completed_trips">
                <div class="kpi-icon kpi-icon-completed"><i class="fa fa-check-double"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Completed Trips</span>
                    <span class="kpi-value" id="kpi-completed-trips"><?php echo e((string)($kpi['completed_trips'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-check-circle"></i> Delivered</span>
                </div>
                <div class="kpi-mini-chart" id="mini-completed-trips"></div>
            </div>

            <!-- Cancelled Trips -->
            <div class="kpi-card" data-kpi="cancelled_trips">
                <div class="kpi-icon kpi-icon-cancelled"><i class="fa fa-times-circle"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Cancelled Trips</span>
                    <span class="kpi-value" id="kpi-cancelled-trips"><?php echo e((string)($kpi['cancelled_trips'] ?? 0)); ?></span>
                    <span class="kpi-trend kpi-trend-down"><i class="fa fa-arrow-down"></i> Cancelled</span>
                </div>
                <div class="kpi-mini-chart" id="mini-cancelled-trips"></div>
            </div>

            <!-- Fleet Utilization -->
            <div class="kpi-card" data-kpi="fleet_utilization">
                <div class="kpi-icon kpi-icon-utilization"><i class="fa fa-chart-pie"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Fleet Utilization</span>
                    <span class="kpi-value" id="kpi-utilization"><?php echo e((string)($kpi['fleet_utilization'] ?? 0)); ?>%</span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-arrow-up"></i> Efficiency</span>
                </div>
                <div class="kpi-mini-chart" id="mini-utilization"></div>
            </div>

            <!-- Fuel Efficiency -->
            <div class="kpi-card" data-kpi="fuel_efficiency">
                <div class="kpi-icon kpi-icon-fuel-eff"><i class="fa fa-tachometer"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Fuel Efficiency</span>
                    <span class="kpi-value" id="kpi-fuel-eff"><?php echo e((string)($kpi['fuel_efficiency'] ?? 0)); ?> <small>km/L</small></span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-arrow-up"></i> Avg</span>
                </div>
                <div class="kpi-mini-chart" id="mini-fuel-eff"></div>
            </div>

            <!-- Fuel Cost -->
            <div class="kpi-card" data-kpi="fuel_cost">
                <div class="kpi-icon kpi-icon-fuel-cost"><i class="fa fa-gas-pump"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Fuel Cost (MTD)</span>
                    <span class="kpi-value" id="kpi-fuel-cost"><?php echo e(formatCurrency((float)($kpi['fuel_cost'] ?? 0))); ?></span>
                    <span class="kpi-trend kpi-trend-warning"><i class="fa fa-arrow-down"></i> MTD</span>
                </div>
                <div class="kpi-mini-chart" id="mini-fuel-cost"></div>
            </div>

            <!-- Maintenance Cost -->
            <div class="kpi-card" data-kpi="maintenance_cost">
                <div class="kpi-icon kpi-icon-maint-cost"><i class="fa fa-tools"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Maintenance Cost (MTD)</span>
                    <span class="kpi-value" id="kpi-maint-cost"><?php echo e(formatCurrency((float)($kpi['maintenance_cost'] ?? 0))); ?></span>
                    <span class="kpi-trend kpi-trend-warning"><i class="fa fa-arrow-down"></i> MTD</span>
                </div>
                <div class="kpi-mini-chart" id="mini-maint-cost"></div>
            </div>

            <!-- Operational Cost -->
            <div class="kpi-card" data-kpi="operational_cost">
                <div class="kpi-icon kpi-icon-op-cost"><i class="fa fa-file-invoice-dollar"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Operational Cost (MTD)</span>
                    <span class="kpi-value" id="kpi-op-cost"><?php echo e(formatCurrency((float)($kpi['operational_cost'] ?? 0))); ?></span>
                    <span class="kpi-trend kpi-trend-warning"><i class="fa fa-arrow-down"></i> MTD</span>
                </div>
                <div class="kpi-mini-chart" id="mini-op-cost"></div>
            </div>

            <!-- Revenue -->
            <div class="kpi-card" data-kpi="total_revenue">
                <div class="kpi-icon kpi-icon-revenue"><i class="fa fa-money-bill-wave"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Total Revenue</span>
                    <span class="kpi-value" id="kpi-revenue"><?php echo e(formatCurrency((float)($kpi['total_revenue'] ?? 0))); ?></span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-arrow-up"></i> All-time</span>
                </div>
                <div class="kpi-mini-chart" id="mini-revenue"></div>
            </div>

            <!-- Vehicle ROI -->
            <div class="kpi-card" data-kpi="vehicle_roi">
                <div class="kpi-icon kpi-icon-roi"><i class="fa fa-chart-line"></i></div>
                <div class="kpi-content">
                    <span class="kpi-title">Vehicle ROI</span>
                    <span class="kpi-value" id="kpi-roi"><?php echo e((string)($kpi['vehicle_roi'] ?? 0)); ?>%</span>
                    <span class="kpi-trend kpi-trend-up"><i class="fa fa-arrow-up"></i> Return</span>
                </div>
                <div class="kpi-mini-chart" id="mini-roi"></div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Status Panels Row -->
    <div class="grid grid-4 page-section">
        <?php if ($widgets['vehicle_status']): ?>
        <!-- Vehicle Status Panel -->
        <div class="card status-panel">
            <div class="card-header">
                <h3><i class="fa fa-bus"></i> Vehicle Status</h3>
                <span class="card-badge">Live</span>
            </div>
            <div class="status-list" id="vehicle-status-list">
                <?php foreach ($vehicleStatus as $vs): ?>
                <div class="status-item">
                    <span class="status-indicator" style="background:<?php echo e($vs['color']); ?>"></span>
                    <i class="fa <?php echo e($vs['icon']); ?>" style="color:<?php echo e($vs['color']); ?>"></i>
                    <span class="status-label"><?php echo e($vs['label']); ?></span>
                    <span class="status-count"><?php echo e($vs['count']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo e(siteUrl('modules/vehicles/index.php')); ?>" class="card-link">View All Vehicles <i class="fa fa-arrow-right"></i></a>
        </div>
        <?php endif; ?>

        <?php if ($widgets['driver_status']): ?>
        <!-- Driver Status Panel -->
        <div class="card status-panel">
            <div class="card-header">
                <h3><i class="fa fa-user"></i> Driver Status</h3>
                <span class="card-badge">Live</span>
            </div>
            <div class="status-list" id="driver-status-list">
                <?php foreach ($driverStatus as $ds): ?>
                <div class="status-item">
                    <span class="status-indicator" style="background:<?php echo e($ds['color']); ?>"></span>
                    <i class="fa <?php echo e($ds['icon']); ?>" style="color:<?php echo e($ds['color']); ?>"></i>
                    <span class="status-label"><?php echo e($ds['label']); ?></span>
                    <span class="status-count"><?php echo e($ds['count']); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <a href="<?php echo e(siteUrl('modules/drivers/index.php')); ?>" class="card-link">View All Drivers <i class="fa fa-arrow-right"></i></a>
        </div>
        <?php endif; ?>

        <?php if ($widgets['maintenance_alerts']): ?>
        <!-- Maintenance Alerts Panel -->
        <div class="card status-panel">
            <div class="card-header">
                <h3><i class="fa fa-exclamation-triangle"></i> Maintenance Alerts</h3>
                <span class="card-badge badge-warning">Alerts</span>
            </div>
            <div class="status-list">
                <div class="status-item">
                    <span class="status-indicator" style="background:#2563eb"></span>
                    <i class="fa fa-wrench" style="color:#2563eb"></i>
                    <span class="status-label">In Shop</span>
                    <span class="status-count"><?php echo e($maintenanceAlerts['in_shop']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-indicator" style="background:#f59e0b"></span>
                    <i class="fa fa-calendar" style="color:#f59e0b"></i>
                    <span class="status-label">Upcoming (7 days)</span>
                    <span class="status-count"><?php echo e($maintenanceAlerts['upcoming']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-indicator" style="background:#ef4444"></span>
                    <i class="fa fa-clock-o" style="color:#ef4444"></i>
                    <span class="status-label">Overdue</span>
                    <span class="status-count"><?php echo e($maintenanceAlerts['overdue']); ?></span>
                </div>
                <div class="status-item-divider"></div>
                <div class="status-item">
                    <span class="status-indicator" style="background:#8b5cf6"></span>
                    <i class="fa fa-id-card" style="color:#8b5cf6"></i>
                    <span class="status-label">Insurance Expired</span>
                    <span class="status-count"><?php echo e($documentAlerts['insurance_expired']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-indicator" style="background:#f59e0b"></span>
                    <i class="fa fa-id-card-o" style="color:#f59e0b"></i>
                    <span class="status-label">Fitness Expiring</span>
                    <span class="status-count"><?php echo e($documentAlerts['fitness_expiring']); ?></span>
                </div>
            </div>
            <a href="<?php echo e(siteUrl('modules/maintenance/index.php')); ?>" class="card-link">Manage Maintenance <i class="fa fa-arrow-right"></i></a>
        </div>
        <?php endif; ?>

        <?php if ($widgets['license_alerts']): ?>
        <!-- License Alerts Panel -->
        <div class="card status-panel">
            <div class="card-header">
                <h3><i class="fa fa-id-card"></i> License Alerts</h3>
                <span class="card-badge <?php echo $licenseAlerts['expired'] > 0 ? 'badge-danger' : 'badge-success'; ?>"><?php echo $licenseAlerts['expired'] > 0 ? 'Critical' : 'OK'; ?></span>
            </div>
            <div class="status-list">
                <div class="status-item">
                    <span class="status-indicator" style="background:#ef4444"></span>
                    <i class="fa fa-times-circle" style="color:#ef4444"></i>
                    <span class="status-label">Expired</span>
                    <span class="status-count"><?php echo e((string)$licenseAlerts['expired']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-indicator" style="background:#f59e0b"></span>
                    <i class="fa fa-exclamation-circle" style="color:#f59e0b"></i>
                    <span class="status-label">Expiring in 7 days</span>
                    <span class="status-count"><?php echo e((string)$licenseAlerts['expiring_7_days']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-indicator" style="background:#f59e0b"></span>
                    <i class="fa fa-clock-o" style="color:#f59e0b"></i>
                    <span class="status-label">Expiring in 15 days</span>
                    <span class="status-count"><?php echo e((string)$licenseAlerts['expiring_15_days']); ?></span>
                </div>
                <div class="status-item">
                    <span class="status-indicator" style="background:#22c55e"></span>
                    <i class="fa fa-check-circle" style="color:#22c55e"></i>
                    <span class="status-label">Expiring in 30 days</span>
                    <span class="status-count"><?php echo e((string)$licenseAlerts['expiring_30_days']); ?></span>
                </div>
            </div>
            <a href="<?php echo e(siteUrl('modules/drivers/index.php')); ?>" class="card-link">Manage Drivers <i class="fa fa-arrow-right"></i></a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Analytics Charts Section -->
    <section class="analytics-section page-section">
        <div class="section-header">
            <h2><i class="fa fa-chart-bar"></i> Analytics & Reports</h2>
            <div class="section-header-actions">
                <button type="button" class="btn btn-sm btn-secondary" onclick="refreshCharts()"><i class="fa fa-refresh"></i> Refresh</button>
            </div>
        </div>

        <div class="charts-grid">
            <!-- Monthly Trips Chart -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-route"></i> Monthly Trips</h3>
                    <span class="chart-type-badge">Bar</span>
                </div>
                <div class="chart-container">
                    <canvas id="chart-monthly-trips"></canvas>
                </div>
            </div>

            <!-- Revenue vs Expense Chart -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-money"></i> Revenue vs Expenses</h3>
                    <span class="chart-type-badge">Line</span>
                </div>
                <div class="chart-container">
                    <canvas id="chart-revenue-expense"></canvas>
                </div>
            </div>

            <!-- Vehicle Status Doughnut -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-bus"></i> Vehicle Status</h3>
                    <span class="chart-type-badge">Doughnut</span>
                </div>
                <div class="chart-container chart-container-sm">
                    <canvas id="chart-vehicle-status"></canvas>
                </div>
            </div>

            <!-- Driver Status Doughnut -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-user"></i> Driver Status</h3>
                    <span class="chart-type-badge">Doughnut</span>
                </div>
                <div class="chart-container chart-container-sm">
                    <canvas id="chart-driver-status"></canvas>
                </div>
            </div>

            <!-- Monthly Revenue -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-trend-up"></i> Monthly Revenue</h3>
                    <span class="chart-type-badge">Area</span>
                </div>
                <div class="chart-container">
                    <canvas id="chart-monthly-revenue"></canvas>
                </div>
            </div>

            <!-- Monthly Expenses -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-trend-down"></i> Monthly Expenses</h3>
                    <span class="chart-type-badge">Area</span>
                </div>
                <div class="chart-container">
                    <canvas id="chart-monthly-expenses"></canvas>
                </div>
            </div>

            <!-- Fuel Consumption -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-gas-pump"></i> Fuel Consumption</h3>
                    <span class="chart-type-badge">Bar</span>
                </div>
                <div class="chart-container">
                    <canvas id="chart-fuel-consumption"></canvas>
                </div>
            </div>

            <!-- Maintenance Cost -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-tools"></i> Maintenance Cost</h3>
                    <span class="chart-type-badge">Bar</span>
                </div>
                <div class="chart-container">
                    <canvas id="chart-maintenance-cost"></canvas>
                </div>
            </div>

            <!-- Fleet Utilization Trend -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-chart-pie"></i> Fleet Utilization</h3>
                    <span class="chart-type-badge">Line</span>
                </div>
                <div class="chart-container">
                    <canvas id="chart-fleet-utilization"></canvas>
                </div>
            </div>

            <!-- Top Vehicles (Horizontal Bar) -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-trophy"></i> Top Vehicles by Revenue</h3>
                    <span class="chart-type-badge">Horizontal Bar</span>
                </div>
                <div class="chart-container">
                    <canvas id="chart-top-vehicles"></canvas>
                </div>
            </div>

            <!-- Top Drivers (Horizontal Bar) -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-star"></i> Top Drivers by Revenue</h3>
                    <span class="chart-type-badge">Horizontal Bar</span>
                </div>
                <div class="chart-container">
                    <canvas id="chart-top-drivers"></canvas>
                </div>
            </div>

            <!-- ROI Chart -->
            <div class="card chart-card">
                <div class="chart-card-header">
                    <h3><i class="fa fa-chart-line"></i> Revenue vs Cost Overview</h3>
                    <span class="chart-type-badge">Pie</span>
                </div>
                <div class="chart-container chart-container-sm">
                    <canvas id="chart-roi"></canvas>
                </div>
            </div>
        </div>
    </section>

    <!-- Recent Trips & Activities Row -->
    <div class="grid grid-2 page-section">
        <?php if ($widgets['recent_trips']): ?>
        <!-- Recent Trips -->
        <div class="card table-card">
            <div class="card-header">
                <h3><i class="fa fa-route"></i> Recent Trips</h3>
                <a href="<?php echo e(siteUrl('modules/trips/index.php')); ?>" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="table-responsive" id="recent-trips-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Trip #</th>
                            <th>Vehicle</th>
                            <th>Driver</th>
                            <th>Route</th>
                            <th>Status</th>
                            <th>Dispatch</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="recent-trips-body">
                        <?php if (empty($recentTrips)): ?>
                        <tr><td colspan="7" class="empty-state">No trips recorded yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($recentTrips as $trip): ?>
                        <tr>
                            <td><a href="<?php echo e(siteUrl('modules/trips/index.php?id=' . $trip['id'])); ?>" class="trip-link"><?php echo e($trip['trip_number']); ?></a></td>
                            <td><?php echo e($trip['vehicle_name'] ?? $trip['registration_number']); ?></td>
                            <td><?php echo e($trip['driver_name']); ?></td>
                            <td><span class="route-text"><?php echo e($trip['origin']); ?> → <?php echo e($trip['destination']); ?></span></td>
                            <td><span class="badge badge-<?php echo strtolower($trip['status'] === 'Completed' ? 'success' : ($trip['status'] === 'Cancelled' ? 'danger' : ($trip['status'] === 'Dispatched' ? 'info' : ($trip['status'] === 'Draft' ? 'secondary' : 'warning')))); ?>"><?php echo e($trip['status']); ?></span></td>
                            <td><?php echo e($trip['dispatch_at'] ? date('d M H:i', strtotime($trip['dispatch_at'])) : '—'); ?></td>
                            <td><a href="<?php echo e(siteUrl('modules/trips/index.php?id=' . $trip['id'])); ?>" class="btn btn-sm btn-outline"><i class="fa fa-eye"></i></a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($widgets['activities']): ?>
        <!-- Recent Activities -->
        <div class="card activity-card">
            <div class="card-header">
                <h3><i class="fa fa-clock"></i> Recent Activities</h3>
                <span class="card-badge">Live Feed</span>
            </div>
            <div class="activity-timeline" id="activity-timeline">
                <?php if (empty($activities)): ?>
                <div class="empty-state">No recent activities.</div>
                <?php else: ?>
                <?php foreach ($activities as $activity): ?>
                <div class="activity-item">
                    <div class="activity-dot"></div>
                    <div class="activity-content">
                        <p class="activity-action"><?php echo e($activity['action']); ?></p>
                        <p class="activity-desc"><?php echo e($activity['description'] ?? ''); ?></p>
                        <span class="activity-meta">
                            <span class="activity-user"><i class="fa fa-user"></i> <?php echo e($activity['user_name'] ?? 'System'); ?></span>
                            <span class="activity-time"><i class="fa fa-clock-o"></i> <?php echo e(date('d M H:i', strtotime($activity['created_at']))); ?></span>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
            <a href="<?php echo e(siteUrl('modules/activity/logs.php')); ?>" class="card-link">View All Activities <i class="fa fa-arrow-right"></i></a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Notifications & Quick Actions -->
    <div class="grid page-section" style="grid-template-columns: 3fr 1fr;">
        <?php if ($widgets['notifications_panel']): ?>
        <!-- Notifications Panel -->
        <div class="card notification-card">
            <div class="card-header">
                <h3><i class="fa fa-bell"></i> Notifications</h3>
                <div class="card-header-actions">
                    <span class="notification-count-badge" id="unread-count"><?php echo e($unreadNotifications); ?></span>
                    <a href="<?php echo e(siteUrl('modules/notifications/index.php')); ?>" class="btn btn-sm btn-secondary">View All</a>
                </div>
            </div>
            <div class="notification-list" id="notification-list">
                <?php if (empty($notifications)): ?>
                <div class="empty-state">No notifications.</div>
                <?php else: ?>
                <?php foreach ($notifications as $notif): ?>
                <div class="notification-item <?php echo $notif['is_read'] ? '' : 'notification-unread'; ?>" data-id="<?php echo e($notif['id']); ?>">
                    <div class="notification-icon">
                        <i class="fa <?php echo $notif['priority'] === 'High' ? 'fa-exclamation-circle text-danger' : ($notif['priority'] === 'Medium' ? 'fa-info-circle text-warning' : 'fa-bell text-muted'); ?>"></i>
                    </div>
                    <div class="notification-content">
                        <p class="notification-title"><?php echo e($notif['title']); ?></p>
                        <p class="notification-message"><?php echo e(strlen($notif['message']) > 80 ? substr($notif['message'], 0, 80) . '...' : $notif['message']); ?></p>
                        <span class="notification-time"><?php echo e(date('d M H:i', strtotime($notif['created_at']))); ?></span>
                        <?php if ($notif['priority'] === 'High'): ?>
                        <span class="badge badge-danger">High Priority</span>
                        <?php elseif ($notif['priority'] === 'Medium'): ?>
                        <span class="badge badge-warning">Medium</span>
                        <?php endif; ?>
                    </div>
                    <?php if (!$notif['is_read']): ?>
                    <button class="notification-mark-read" onclick="markNotificationRead(<?php echo e($notif['id']); ?>)" title="Mark as read"><i class="fa fa-check"></i></button>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="card quick-actions-card">
            <div class="card-header">
                <h3><i class="fa fa-bolt"></i> Quick Actions</h3>
            </div>
            <div class="quick-actions-grid">
                <button class="quick-action-btn" onclick="window.location.href='<?php echo e(siteUrl('modules/vehicles/add.php')); ?>'">
                    <span class="qab-icon qab-vehicles"><i class="fa fa-bus"></i></span>
                    <span class="qab-label">Add Vehicle</span>
                </button>
                <button class="quick-action-btn" onclick="window.location.href='<?php echo e(siteUrl('modules/drivers/add.php')); ?>'">
                    <span class="qab-icon qab-drivers"><i class="fa fa-user-plus"></i></span>
                    <span class="qab-label">Add Driver</span>
                </button>
                <button class="quick-action-btn" onclick="window.location.href='<?php echo e(siteUrl('modules/trips/add.php')); ?>'">
                    <span class="qab-icon qab-trips"><i class="fa fa-route"></i></span>
                    <span class="qab-label">Create Trip</span>
                </button>
                <button class="quick-action-btn" onclick="window.location.href='<?php echo e(siteUrl('modules/fuel/add.php')); ?>'">
                    <span class="qab-icon qab-fuel"><i class="fa fa-gas-pump"></i></span>
                    <span class="qab-label">Add Fuel</span>
                </button>
                <button class="quick-action-btn" onclick="window.location.href='<?php echo e(siteUrl('modules/expenses/add.php')); ?>'">
                    <span class="qab-icon qab-expenses"><i class="fa fa-wallet"></i></span>
                    <span class="qab-label">Add Expense</span>
                </button>
                <button class="quick-action-btn" onclick="window.location.href='<?php echo e(siteUrl('modules/maintenance/add.php')); ?>'">
                    <span class="qab-icon qab-maintenance"><i class="fa fa-wrench"></i></span>
                    <span class="qab-label">Maintenance</span>
                </button>
                <button class="quick-action-btn" onclick="window.location.href='<?php echo e(siteUrl('modules/reports/index.php')); ?>'">
                    <span class="qab-icon qab-reports"><i class="fa fa-file-alt"></i></span>
                    <span class="qab-label">Reports</span>
                </button>
                <button class="quick-action-btn" onclick="window.location.href='<?php echo e(siteUrl('modules/analytics/index.php')); ?>'">
                    <span class="qab-icon qab-analytics"><i class="fa fa-chart-line"></i></span>
                    <span class="qab-label">Analytics</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Pass initial data to JavaScript
window.__DASHBOARD_DATA__ = {
    kpi: <?php echo json_encode($kpi); ?>,
    vehicleStatus: <?php echo json_encode($vehicleStatus); ?>,
    driverStatus: <?php echo json_encode($driverStatus); ?>,
    maintenanceAlerts: <?php echo json_encode($maintenanceAlerts); ?>,
    licenseAlerts: <?php echo json_encode($licenseAlerts); ?>,
    documentAlerts: <?php echo json_encode($documentAlerts); ?>,
    unreadCount: <?php echo json_encode($unreadNotifications); ?>,
    widgetAccess: <?php echo json_encode($widgets); ?>
};
</script>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
</write_to_file>
<task_progress>
- [x] Create database dashboard views
- [x] Create dashboard functions
- [x] Create AJAX endpoint
- [x] Rewrite main dashboard page
- [ ] Rewrite dashboard CSS
- [ ] Rewrite dashboard JS
- [ ] Rewrite charts JS
- [ ] Test implementation
</task_progress>
</write_to_file>
