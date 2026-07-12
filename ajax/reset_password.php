<?php
/** JSON reset-token consumption endpoint. */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) jsonResponse(['ok'=>false],400);
$password=(string)($_POST['password'] ?? ''); $ok=passwordPolicyError($password)===null && hash_equals($password,(string)($_POST['password_confirmation'] ?? '')) && resetPasswordWithToken((string)($_POST['token'] ?? ''),$password);
jsonResponse(['ok'=>$ok,'message'=>$ok?'Password reset.':'Unable to reset password.'],$ok?200:422);
