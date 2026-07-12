<?php
/** Reusable native dialog. Set $modalId, $modalTitle and $modalContent. */
declare(strict_types=1);
?>
<dialog id="<?= e((string)($modalId ?? 'modal')) ?>"><h2><?= e((string)($modalTitle ?? 'Dialog')) ?></h2><?= (string)($modalContent ?? '') ?><form method="dialog"><button class="btn btn-secondary">Close</button></form></dialog>
