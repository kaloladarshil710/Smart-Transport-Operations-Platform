<?php
/**
 * Email system module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Email System</h1>
    <p class="page-subtitle">Send notifications, reminders and operational emails through the mail layer.</p>
    <div class="card dashboard-card">
        <p>Mail templates, reminder workflows and SMTP configuration are available for the production release path.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
