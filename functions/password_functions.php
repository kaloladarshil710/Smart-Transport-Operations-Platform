<?php
/** Password policy and reset-token operations. */
declare(strict_types=1);

function passwordPolicyError(string $password): ?string
{
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/\d/', $password) || !preg_match('/[^a-zA-Z\d]/', $password)) return 'Use at least 8 characters with upper- and lowercase letters, a number, and a symbol.';
    return null;
}
function createPasswordReset(string $email): void
{
    $statement=getDb()->prepare('SELECT id FROM users WHERE email=? AND status="Active" AND deleted_at IS NULL'); $statement->execute([$email]);
    if (!$statement->fetch()) return;
    $token=bin2hex(random_bytes(32)); getDb()->prepare('UPDATE password_resets SET used=1 WHERE email=? AND used=0')->execute([$email]);
    getDb()->prepare('INSERT INTO password_resets (email,token,expires_at) VALUES (?,?,DATE_ADD(NOW(),INTERVAL 30 MINUTE))')->execute([$email,hash('sha256',$token)]);
    $link = siteUrl('reset_password.php?token=' . rawurlencode($token));
    sendMail($email, 'Reset your TransitOps password', '<p>Use this secure link within 30 minutes:</p><p><a href="' . e($link) . '">Reset password</a></p>');
}
function resetPasswordWithToken(string $token, string $password): bool
{
    $statement=getDb()->prepare('SELECT id,email FROM password_resets WHERE token=? AND used=0 AND expires_at>NOW() ORDER BY id DESC LIMIT 1'); $statement->execute([hash('sha256',$token)]); $reset=$statement->fetch();
    if (!$reset) return false;
    getDb()->prepare('UPDATE users SET password_hash=?,remember_token=NULL WHERE email=?')->execute([password_hash($password,PASSWORD_DEFAULT),$reset['email']]);
    getDb()->prepare('UPDATE password_resets SET used=1 WHERE id=?')->execute([$reset['id']]); return true;
}
