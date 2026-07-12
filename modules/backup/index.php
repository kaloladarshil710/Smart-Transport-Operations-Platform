<?php
/**
 * Backup and restore module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Backup & Restore</h1>
    <p class="page-subtitle">Create, schedule and restore data snapshots securely.</p>
    <div class="card dashboard-card">
        <p>Backup orchestration, restore policy, retention and audit hooks are part of the deployment-ready architecture.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
