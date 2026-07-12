<?php
/**
 * Testing and QA module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Testing</h1>
    <p class="page-subtitle">QA, validation and acceptance testing workflow for the platform.</p>
    <div class="card dashboard-card">
        <p>Manual and automated test plans, regression checks and validation scripts are included in the package structure.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
