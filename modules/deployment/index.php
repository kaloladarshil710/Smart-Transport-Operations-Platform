<?php
/**
 * Deployment module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Deployment</h1>
    <p class="page-subtitle">Runbook for deploying the platform on XAMPP and production web servers.</p>
    <div class="card dashboard-card">
        <p>Deployment scripts, environment settings and infrastructure notes are packaged for direct launch.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
