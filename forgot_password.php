<?php
/** Password-reset request page. Never discloses account existence. */
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) { setFlash('danger','Your form session expired.'); redirect('forgot_password.php'); }
    $email=trim((string)($_POST['email'] ?? '')); if (filter_var($email,FILTER_VALIDATE_EMAIL)) createPasswordReset($email);
    setFlash('success','If that account exists, a password-reset link has been requested.'); redirect('login.php');
}
require __DIR__ . '/includes/header.php';
?>
<main class="auth-shell"><section class="auth-card card"><h2>Reset your password</h2><p>Enter your work email and we will send reset instructions.</p><form method="post" data-validate="true"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><div class="form-group"><label for="email">Email address</label><input id="email" name="email" type="email" autocomplete="email" required></div><button class="btn btn-primary" type="submit">Request reset link</button><a class="auth-link" href="login.php">Back to sign in</a></form></section></main>
<?php require __DIR__ . '/includes/footer.php'; ?>
