<?php
/** Authenticated password change endpoint and page. */
declare(strict_types=1);
require_once __DIR__ . '/config/config.php'; requireAuth();
if ($_SERVER['REQUEST_METHOD']==='POST') {
 if (!verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) { setFlash('danger','Your form session expired.'); redirect('change_password.php'); }
 $current=(string)($_POST['current_password'] ?? ''); $new=(string)($_POST['new_password'] ?? ''); $confirm=(string)($_POST['confirm_password'] ?? ''); $user=currentUser();
 $error=passwordPolicyError($new); if (!password_verify($current,(string)$user['password_hash']) || $error || !hash_equals($new,$confirm)) { setFlash('danger',$error ?: 'Current password is invalid or confirmation does not match.'); redirect('change_password.php'); }
 getDb()->prepare('UPDATE users SET password_hash=?,remember_token=NULL WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),(int)$user['id']]); logActivity('Password changed'); setFlash('success','Password updated.'); redirect('modules/profile/index.php');
}
require __DIR__ . '/includes/header.php'; ?><main class="auth-shell"><section class="auth-card card"><h2>Change password</h2><form method="post"><input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>"><div class="form-group"><label>Current password</label><input name="current_password" type="password" required></div><div class="form-group"><label>New password</label><input name="new_password" type="password" required></div><div class="form-group"><label>Confirm password</label><input name="confirm_password" type="password" required></div><button class="btn btn-primary">Update password</button></form></section></main><?php require __DIR__ . '/includes/footer.php'; ?>
