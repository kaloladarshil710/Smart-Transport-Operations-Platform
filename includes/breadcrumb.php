<?php
/** Reusable accessible breadcrumb navigation. */
declare(strict_types=1);
?>
<nav class="breadcrumb" aria-label="Breadcrumb">
    <?php foreach (($breadcrumbs ?? []) as $index => $crumb): ?>
        <?php if (isset($crumb['url']) && $index < count($breadcrumbs) - 1): ?>
            <a href="<?= e(siteUrl($crumb['url'])) ?>"><?= e($crumb['label']) ?></a><span aria-hidden="true">/</span>
        <?php else: ?><span aria-current="page"><?= e($crumb['label']) ?></span><?php endif; ?>
    <?php endforeach; ?>
</nav>
