<?php
/** Consumes a one-time, short-lived password reset token. */
declare(strict_types=1);
require_once __DIR__ . '/config/config.php'; $token=(string)($_GET['token'] ?? $_POST['token'] ?? '');
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if (!verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) { setFlash('danger','Your form session expired.'); redirect('reset_password.php'); }
 $password=(string)($_POST['password'] ?? ''); $confirm=(string)($_POST['password_confirmation'] ?? ''); $error=passwordPolicyError($password);
 if ($error || !hash_equals($password,$confirm) || !resetPasswordWithToken($token,$password)) { setFlash('danger',$error ?: 'The reset link is invalid, expired, or passwords do not match.'); redirect('reset_password.php?token='.urlencode($token)); }
 setFlash('success','Your password has been reset. Please sign in.'); redirect('login.php');
}
require __DIR__ . '/includes/header.php';
?><main class="auth-shell"><section class="auth-card card"><h2>Choose a new password</h2><p>Use 8+ characters, uppercase, lowercase, number, and symbol.</p><form method="post" data-validate="true"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><input type="hidden" name="token" value="<?= e($token) ?>"><div class="form-group"><label for="password">New password</label><input id="password" name="password" type="password" autocomplete="new-password" required></div><div class="form-group"><label for="password_confirmation">Confirm password</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required></div><button class="btn btn-primary" type="submit">Reset password</button></form></section></main><?php require __DIR__ . '/includes/footer.php'; ?>
