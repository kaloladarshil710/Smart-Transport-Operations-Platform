<?php
/** Displays a one-time flash message when included in a page. */
declare(strict_types=1);
$alert = getFlash();
if ($alert):
?>
<div class="flash-message flash-<?= e($alert['type']) ?>" role="alert"><?= e($alert['message']) ?></div>
<?php endif; ?>
