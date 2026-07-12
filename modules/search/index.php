<?php
/**
 * Global search module.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();

$query = trim($_GET['q'] ?? '');
$results = [];

if ($query !== '') {
    $stmt = getDb()->prepare('SELECT registration_number AS label, "Vehicle" AS type FROM vehicles WHERE registration_number LIKE ? LIMIT 5');
    $stmt->execute(['%' . $query . '%']);
    $results = array_merge($results, $stmt->fetchAll());
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Global Search</h1>
    <p class="page-subtitle">Search vehicles, drivers, trips and modules instantly.</p>
    <div class="card dashboard-card">
        <form method="get" action="index.php">
            <div class="form-group">
                <label>Search</label>
                <input type="text" name="q" value="<?php echo e($query); ?>">
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>
        <?php if ($query !== ''): ?>
        <div class="mt-3">
            <?php foreach ($results as $result): ?>
            <div class="card mt-2" style="padding: 12px;">
                <strong><?php echo e($result['label']); ?></strong>
                <p class="mb-2">Type: <?php echo e($result['type']); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
