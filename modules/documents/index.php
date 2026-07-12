<?php
/**
 * Document management module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Documents</h1>
    <p class="page-subtitle">Store and review vehicle and driver documents securely.</p>
    <div class="card dashboard-card">
        <p>Vehicle and driver document uploads, validation and document indexing are integrated into the enterprise structure.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
