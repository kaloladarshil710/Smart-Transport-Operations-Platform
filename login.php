<?php
/**
 * Login page for TransitOps.
 */
declare(strict_types=1);

require_once __DIR__ . '/config/config.php';

if (currentUser()) {
    redirect('dashboard.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        setFlash('danger', 'Email and password are required.');
        redirect('login.php');
    }

    $stmt = getDb()->prepare('SELECT id, full_name, email, password_hash, role, status FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash']) && $user['status'] === 'Active') {
        loginUser($user);
        regenerateSession();
        setFlash('success', 'Welcome back!');
        redirect('dashboard.php');
    }

    setFlash('danger', 'Invalid credentials.');
    redirect('login.php');
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-shell">
    <div class="auth-card card">
        <h2>Sign in to TransitOps</h2>
        <p>Enterprise fleet operations platform</p>
        <form method="post" action="login.php" data-validate="true">
            <input type="hidden" name="csrf_token" value="<?php echo e(generateCsrfToken()); ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary">Sign In</button>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
