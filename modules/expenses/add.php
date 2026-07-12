<?php
/**
 * Add expense form.
 */
declare(strict_types=1);

require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('expenses');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? '')) {
        setFlash('danger', 'Invalid CSRF token.');
        redirect('modules/expenses/add.php');
    }

    $title = trim($_POST['title'] ?? '');
    $amount = (float)($_POST['amount'] ?? 0);
    $type = trim($_POST['expense_type'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $expenseDate = trim($_POST['expense_date'] ?? '');

    if ($title === '' || $amount <= 0 || $type === '' || $expenseDate === '') {
        setFlash('danger', 'Expense details are required.');
        redirect('modules/expenses/add.php');
    }

    $insert = getDb()->prepare('INSERT INTO expenses (title, amount, expense_type, description, expense_date) VALUES (?, ?, ?, ?, ?)');
    $insert->execute([$title, $amount, $type, $description, $expenseDate]);
    setFlash('success', 'Expense recorded.');
    redirect('modules/expenses/index.php');
}

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <h1 class="page-title">Add Expense</h1>
    <p class="page-subtitle">Track operational and miscellaneous costs consistently.</p>
    <div class="card dashboard-card">
        <form method="post" action="add.php" data-validate="true">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
            <div class="form-grid">
                <div class="form-group"><label>Title</label><input name="title" required></div>
                <div class="form-group"><label>Amount</label><input type="number" step="0.01" name="amount" required></div>
                <div class="form-group"><label>Expense Type</label><input name="expense_type" required></div>
                <div class="form-group"><label>Expense Date</label><input type="date" name="expense_date" required></div>
                <div class="form-group"><label>Description</label><textarea name="description"></textarea></div>
            </div>
            <div class="form-actions">
                <button class="btn btn-primary" type="submit">Save Expense</button>
                <a class="btn btn-secondary" href="index.php">Cancel</a>
            </div>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
