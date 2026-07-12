<?php
/** Accessible select; $options is [value => label]. */
declare(strict_types=1);
?>
<div class="form-group"><label for="field-<?= e((string)$name) ?>"><?= e((string)($label ?? 'Select')) ?></label><select id="field-<?= e((string)$name) ?>" name="<?= e((string)$name) ?>"<?= !empty($required) ? ' required' : '' ?>><?php foreach (($options ?? []) as $optionValue => $optionLabel): ?><option value="<?= e((string)$optionValue) ?>"<?= (string)$optionValue === (string)($value ?? '') ? ' selected' : '' ?>><?= e((string)$optionLabel) ?></option><?php endforeach; ?></select></div>
