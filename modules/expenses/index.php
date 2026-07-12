<?php
/**
 * Expenses module index.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('expenses');

$stmt = getDb()->query('SELECT id, title, amount, expense_type, expense_date FROM expenses ORDER BY id DESC');
$expenses = $stmt->fetchAll();

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <div class="page-section d-flex justify-between align-center">
        <div>
            <h1 class="page-title">Expense Management</h1>
            <p class="page-subtitle">Record fleet-related spending and track categories.</p>
        </div>
        <a class="btn btn-primary" href="add.php">Add Expense</a>
    </div>
    <div class="card table-card">
        <div class="table-responsive">
            <table>
                <thead><tr><th>Title</th><th>Type</th><th>Amount</th><th>Date</th></tr></thead>
                <tbody>
                    <?php foreach ($expenses as $expense): ?>
                    <tr>
                        <td><?php echo e($expense['title']); ?></td>
                        <td><?php echo e($expense['expense_type']); ?></td>
                        <td><?php echo e(formatCurrency((float)$expense['amount'])); ?></td>
                        <td><?php echo e(formatDate($expense['expense_date'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
