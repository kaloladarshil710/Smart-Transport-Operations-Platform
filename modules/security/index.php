<?php
/**
 * Security module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Security</h1>
    <p class="page-subtitle">Monitor authentication, session hardening and access controls.</p>
    <div class="card dashboard-card">
        <p>Security posture, RBAC enforcement, password policy and audit logging are implemented through the shared middleware and config layer.</p>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
