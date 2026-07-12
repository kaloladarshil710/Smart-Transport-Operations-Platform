<?php
/**
 * Analytics module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('analytics');

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Analytics</h1>
    <p class="page-subtitle">Review KPI trends, cost drivers and fleet utilization.</p>
    <div class="card dashboard-card">
        <p>Analytics visuals, ROI calculations and operational trend reporting are prepared for the next phase of the enterprise release.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
