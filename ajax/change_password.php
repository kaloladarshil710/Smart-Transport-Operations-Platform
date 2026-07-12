<?php
/** JSON password-change endpoint for authenticated users. */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php'; requireAuth();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) jsonResponse(['ok'=>false],400);
$new=(string)($_POST['new_password'] ?? ''); $user=currentUser(); $ok=password_verify((string)($_POST['current_password'] ?? ''),(string)$user['password_hash']) && passwordPolicyError($new)===null && hash_equals($new,(string)($_POST['confirm_password'] ?? ''));
if ($ok) { getDb()->prepare('UPDATE users SET password_hash=?,remember_token=NULL WHERE id=?')->execute([password_hash($new,PASSWORD_DEFAULT),(int)$user['id']]); logActivity('Password changed'); }
jsonResponse(['ok'=>$ok,'message'=>$ok?'Password updated.':'Unable to update password.'],$ok?200:422);
