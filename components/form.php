<?php
/** Opens a CSRF-protected form. Set $action and $method before inclusion. */
declare(strict_types=1);
?>
<form action="<?= e((string)($action ?? '')) ?>" method="<?= e(strtoupper((string)($method ?? 'post'))) ?>" novalidate><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
