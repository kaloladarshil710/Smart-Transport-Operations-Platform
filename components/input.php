<?php
/** Accessible text input. Set $name, $label, $value, $type and $required. */
declare(strict_types=1);
$inputId = 'field-' . preg_replace('/[^a-zA-Z0-9_-]/', '-', (string)($name ?? 'input'));
?>
<div class="form-group"><label for="<?= e($inputId) ?>"><?= e((string)($label ?? 'Field')) ?></label><input id="<?= e($inputId) ?>" name="<?= e((string)($name ?? '')) ?>" type="<?= e((string)($type ?? 'text')) ?>" value="<?= e((string)($value ?? '')) ?>"<?= !empty($required) ? ' required' : '' ?>></div>
