<?php
/** Renders numbered pagination. Set $page, $totalPages, and $paginationUrl before including. */
declare(strict_types=1);
if (($totalPages ?? 0) > 1):
?>
<nav class="pagination" aria-label="Pagination">
<?php for ($number = 1; $number <= $totalPages; $number++): ?>
 <a class="btn <?= $number === $page ? 'btn-primary' : 'btn-secondary' ?>" href="<?= e($paginationUrl . $number) ?>"><?= $number ?></a>
<?php endfor; ?>
</nav>
<?php endif; ?>
