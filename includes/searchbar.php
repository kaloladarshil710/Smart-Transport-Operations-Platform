<?php
/** Search input for GET-based resource listings. */
declare(strict_types=1);
?>
<label class="searchbar"><span class="sr-only">Search</span><input type="search" name="q" value="<?= e((string)($_GET['q'] ?? '')) ?>" placeholder="Search…"></label>
