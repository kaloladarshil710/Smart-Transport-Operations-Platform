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
    if (!verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) { setFlash('danger', 'Your form session expired. Please try again.'); redirect('login.php'); }
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') { setFlash('danger', 'Enter a valid email and password.'); redirect('login.php'); }
    [$authenticated, $message] = authenticateCredentials($email, $password, !empty($_POST['remember_me']));
    if ($authenticated) { setFlash('success', $message); redirect('dashboard.php'); }
    setFlash('danger', $message);
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
                <div class="password-field"><input type="password" id="password" name="password" autocomplete="current-password" required><button class="password-toggle" type="button" data-password-toggle="password" aria-label="Show password"><i class="fa fa-eye"></i></button></div>
            </div>
            <div class="auth-options"><label><input type="checkbox" name="remember_me" value="1"> Remember me for 30 days</label><a href="forgot_password.php">Forgot password?</a></div>
            <button type="submit" class="btn btn-primary">Sign In</button>
            <a class="auth-link" href="signup.php">Request an account</a>
        </form>
    </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>
