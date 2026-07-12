<?php
/** Status badge component. Set $label and optionally $tone before inclusion. */
declare(strict_types=1);
$tone = $tone ?? 'secondary';
?>
<span class="badge badge-<?= e($tone) ?>"><?= e((string)($label ?? '')) ?></span>
