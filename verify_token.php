<?php
/** Checks whether a password-reset token is still usable without consuming it. */
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
$token=(string)($_GET['token'] ?? ''); $statement=getDb()->prepare('SELECT 1 FROM password_resets WHERE token=? AND used=0 AND expires_at>NOW() LIMIT 1'); $statement->execute([hash('sha256',$token)]);
if (!$statement->fetch()) { setFlash('danger','This reset link is invalid or has expired.'); redirect('forgot_password.php'); }
redirect('reset_password.php?token='.rawurlencode($token));
