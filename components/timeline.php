<?php
/** Chronological timeline. $timelineItems contains arrays with date, title and detail. */
declare(strict_types=1);
?>
<ol class="timeline"><?php foreach (($timelineItems ?? []) as $item): ?><li><time><?= e((string)$item['date']) ?></time><strong><?= e((string)$item['title']) ?></strong><p><?= e((string)($item['detail'] ?? '')) ?></p></li><?php endforeach; ?></ol>
