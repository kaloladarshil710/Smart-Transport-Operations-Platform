<?php
/**
 * Sidebar navigation.
 */
declare(strict_types=1);

$user = currentUser();
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon">T</div>
        <div>
            <h3>TransitOps</h3>
            <p>Fleet Platform</p>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="<?php echo e(siteUrl('dashboard.php')); ?>" class="nav-link active"><i class="fa fa-home"></i> Dashboard</a>
        <a href="<?php echo e(siteUrl('modules/vehicles/index.php')); ?>" class="nav-link"><i class="fa fa-bus"></i> Vehicles</a>
        <a href="<?php echo e(siteUrl('modules/drivers/index.php')); ?>" class="nav-link"><i class="fa fa-user"></i> Drivers</a>
        <a href="<?php echo e(siteUrl('modules/trips/index.php')); ?>" class="nav-link"><i class="fa fa-road"></i> Trips</a>
        <a href="<?php echo e(siteUrl('modules/maintenance/index.php')); ?>" class="nav-link"><i class="fa fa-wrench"></i> Maintenance</a>
        <a href="<?php echo e(siteUrl('modules/fuel/index.php')); ?>" class="nav-link"><i class="fa fa-gas-pump"></i> Fuel</a>
        <a href="<?php echo e(siteUrl('modules/expenses/index.php')); ?>" class="nav-link"><i class="fa fa-money-bill"></i> Expenses</a>
        <a href="<?php echo e(siteUrl('modules/reports/index.php')); ?>" class="nav-link"><i class="fa fa-file-alt"></i> Reports</a>
        <a href="<?php echo e(siteUrl('modules/analytics/index.php')); ?>" class="nav-link"><i class="fa fa-chart-line"></i> Analytics</a>
        <a href="<?php echo e(siteUrl('modules/notifications/index.php')); ?>" class="nav-link"><i class="fa fa-bell"></i> Notifications</a>
        <?php if (isAdmin()): ?>
        <a href="<?php echo e(siteUrl('modules/users/index.php')); ?>" class="nav-link"><i class="fa fa-users"></i> Users</a>
        <a href="<?php echo e(siteUrl('modules/settings/index.php')); ?>" class="nav-link"><i class="fa fa-cog"></i> Settings</a>
        <?php endif; ?>
    </nav>
</aside>
