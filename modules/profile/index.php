<?php
/**
 * User profile module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">My Profile</h1>
    <p class="page-subtitle">Review personal details and account information.</p>
    <div class="card dashboard-card">
        <p>Profile editing, password changes and account preferences are available for the production release structure.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
