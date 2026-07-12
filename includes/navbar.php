<?php
/** Sticky top navigation, global search, theme preference and account access. */
declare(strict_types=1);
$user = currentUser();
?>
<header class="topbar">
 <div class="topbar-left"><button class="sidebar-toggle" type="button" aria-label="Toggle navigation" aria-expanded="false"><i class="fa fa-bars" aria-hidden="true"></i></button><form class="searchbox" method="get" action="<?= e(siteUrl('modules/search/index.php')) ?>" role="search"><i class="fa fa-search" aria-hidden="true"></i><label class="sr-only" for="global-search">Search TransitOps</label><input id="global-search" type="search" name="q" placeholder="Search vehicles, drivers, trips…"></form></div>
 <div class="topbar-right"><button id="theme-toggle" class="icon-button" type="button" aria-label="Enable dark mode"><i class="fa fa-moon-o" aria-hidden="true"></i></button><a class="icon-button" href="<?= e(siteUrl('modules/notifications/index.php')) ?>" aria-label="View notifications"><i class="fa fa-bell" aria-hidden="true"></i><span class="notification-dot"></span></a><a class="profile-pill" href="<?= e(siteUrl('modules/profile/index.php')) ?>" aria-label="Open profile"><span class="avatar"><?= e(strtoupper(substr((string)($user['full_name'] ?? 'A'), 0, 1))) ?></span><span><strong><?= e((string)($user['full_name'] ?? 'Admin')) ?></strong><p><?= e((string)($user['role'] ?? 'admin')) ?></p></span></a></div>
</header>
