<?php
/** Table shell. Set $tableHead and $tableBody with application-rendered markup. */
declare(strict_types=1);
?>
<div class="table-responsive"><table><thead><?= (string)($tableHead ?? '') ?></thead><tbody><?= (string)($tableBody ?? '') ?></tbody></table></div>
