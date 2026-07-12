<?php
/**
 * Top navigation bar.
 */
declare(strict_types=1);

$user = currentUser();
?>
<header class="topbar">
    <div class="topbar-left">
        <button class="sidebar-toggle" type="button"><i class="fa fa-bars"></i></button>
        <div class="searchbox">
            <i class="fa fa-search"></i>
            <input type="text" placeholder="Search fleet data">
        </div>
    </div>
    <div class="topbar-right">
        <button class="icon-button" type="button"><i class="fa fa-bell"></i></button>
        <div class="profile-pill">
            <div class="avatar">A</div>
            <div>
                <strong><?php echo e($user['full_name'] ?? 'Admin'); ?></strong>
                <p><?php echo e($user['role'] ?? 'admin'); ?></p>
            </div>
        </div>
    </div>
</header>
