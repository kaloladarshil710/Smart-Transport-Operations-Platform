<?php
/** Button component. Set $label, $type, $class and $attributes before inclusion. */
declare(strict_types=1);
?>
<button type="<?= e((string)($type ?? 'button')) ?>" class="btn <?= e((string)($class ?? 'btn-primary')) ?>"<?= (string)($attributes ?? '') ?>><?= e((string)($label ?? 'Submit')) ?></button>
