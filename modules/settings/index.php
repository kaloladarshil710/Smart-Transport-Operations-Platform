<?php
/**
 * Settings module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('settings');

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Settings</h1>
    <p class="page-subtitle">Configure company account, integrations and appearance.</p>
    <div class="card dashboard-card">
        <p>General, company, mail and system settings screens are included in the enterprise-ready structure.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
