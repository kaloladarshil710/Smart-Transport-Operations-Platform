<?php
/** Canvas chart target. Set $chartId and $chartLabel. */
declare(strict_types=1);
?>
<canvas id="<?= e((string)($chartId ?? 'chart')) ?>" aria-label="<?= e((string)($chartLabel ?? 'Chart')) ?>" role="img"></canvas>
