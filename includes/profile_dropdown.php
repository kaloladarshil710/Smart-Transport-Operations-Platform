<?php
/** Current-user profile controls for navbar integrations. */
declare(strict_types=1);
?>
<div class="profile-dropdown"><span><?= e((string)(currentUser()['full_name'] ?? 'Account')) ?></span><a href="<?= e(siteUrl('modules/profile/index.php')) ?>">Profile</a><a href="<?= e(siteUrl('logout.php')) ?>">Sign out</a></div>
