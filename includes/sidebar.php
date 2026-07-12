<?php
/** Fixed application navigation with contextual active state. */
declare(strict_types=1);
$user = currentUser();
$path = (string)parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
$navItems = [
    ['dashboard.php', 'Dashboard', 'fa-home'], ['modules/vehicles/', 'Vehicles', 'fa-bus'],
    ['modules/drivers/', 'Drivers', 'fa-user'], ['modules/trips/', 'Trips', 'fa-route'],
    ['modules/maintenance/', 'Maintenance', 'fa-wrench'], ['modules/fuel/', 'Fuel', 'fa-gas-pump'],
    ['modules/expenses/', 'Expenses', 'fa-wallet'], ['modules/reports/', 'Reports', 'fa-file-alt'],
    ['modules/analytics/', 'Analytics', 'fa-chart-line'], ['modules/notifications/', 'Notifications', 'fa-bell'],
];
?>
<aside class="sidebar" aria-label="Primary navigation">
    <a class="sidebar-brand" href="<?= e(siteUrl('dashboard.php')) ?>" aria-label="TransitOps dashboard"><div class="brand-icon">T</div><div><h3>TransitOps</h3><p>Operations cloud</p></div></a>
    <div class="sidebar-user"><div class="avatar"><?= e(strtoupper(substr((string)($user['full_name'] ?? 'A'), 0, 1))) ?></div><div><strong><?= e((string)($user['full_name'] ?? 'Account')) ?></strong><small><?= e((string)($user['role'] ?? 'User')) ?></small></div></div>
    <nav class="sidebar-nav">
        <p class="sidebar-section">Operations</p>
        <?php foreach ($navItems as [$href, $label, $icon]): $active = str_contains($path, trim($href, '/')); ?>
        <a href="<?= e(siteUrl($href . (str_ends_with($href, '/') ? 'index.php' : '')) ) ?>" class="nav-link<?= $active ? ' active' : '' ?>"><i class="fa <?= e($icon) ?>" aria-hidden="true"></i><span><?= e($label) ?></span></a>
        <?php endforeach; ?>
        <?php if (isAdmin()): ?><p class="sidebar-section">Administration</p>
        <a href="<?= e(siteUrl('modules/users/index.php')) ?>" class="nav-link<?= str_contains($path, '/users/') ? ' active' : '' ?>"><i class="fa fa-users" aria-hidden="true"></i><span>Users</span></a>
        <a href="<?= e(siteUrl('modules/settings/index.php')) ?>" class="nav-link<?= str_contains($path, '/settings/') ? ' active' : '' ?>"><i class="fa fa-cog" aria-hidden="true"></i><span>Settings</span></a><?php endif; ?>
    </nav>
</aside>
