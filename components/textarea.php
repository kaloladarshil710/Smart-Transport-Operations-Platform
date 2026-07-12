<?php
/** Accessible textarea field. */
declare(strict_types=1);
?>
<div class="form-group"><label for="field-<?= e((string)$name) ?>"><?= e((string)($label ?? 'Details')) ?></label><textarea id="field-<?= e((string)$name) ?>" name="<?= e((string)$name) ?>" rows="<?= (int)($rows ?? 4) ?>"><?= e((string)($value ?? '')) ?></textarea></div>
