<?php
/** Enterprise authentication, throttling, remember-me and audit helpers. */
declare(strict_types=1);

function clientIp(): string { return substr((string)($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'), 0, 45); }
function clientAgent(): string { return substr((string)($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'), 0, 255); }

function recordLoginAttempt(?int $userId, bool $success): void
{
    getDb()->prepare('INSERT INTO login_logs (user_id, ip_address, browser, success, login_at) VALUES (?, ?, ?, ?, NOW())')->execute([$userId, clientIp(), clientAgent(), $success ? 1 : 0]);
}

function isLoginRateLimited(string $email): bool
{
    $statement = getDb()->prepare('SELECT COUNT(*) FROM login_logs l LEFT JOIN users u ON u.id=l.user_id WHERE l.success=0 AND l.created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND (l.ip_address=? OR u.email=?)');
    $statement->execute([clientIp(), $email]);
    return (int)$statement->fetchColumn() >= 5;
}

/** Attempts credential authentication without revealing whether an email exists. */
function authenticateCredentials(string $email, string $password, bool $remember = false): array
{
    if (isLoginRateLimited($email)) { return [false, 'Too many failed attempts. Please try again in 15 minutes.']; }
    $statement = getDb()->prepare('SELECT id, full_name, email, password_hash, role, status, phone, avatar_path, approval_status, is_active, rejection_reason FROM users WHERE email=? AND deleted_at IS NULL LIMIT 1');
    $statement->execute([$email]); $user = $statement->fetch();
    if (!$user || !password_verify($password, $user['password_hash'])) {
        recordLoginAttempt($user['id'] ?? null, false);
        return [false, 'Invalid email or password.'];
    }
    if (($user['approval_status'] ?? 'Approved') === 'Pending') { recordLoginAttempt((int)$user['id'], false); return [false, 'Your account is awaiting approval from an Administrator or Fleet Manager.']; }
    if (($user['approval_status'] ?? 'Approved') === 'Rejected') { recordLoginAttempt((int)$user['id'], false); return [false, 'Your registration request has been rejected. ' . ($user['rejection_reason'] ? 'Reason: '.$user['rejection_reason'] : '')]; }
    if ($user['status'] !== 'Active' || !(bool)($user['is_active'] ?? true)) { recordLoginAttempt((int)$user['id'], false); return [false, 'Your account is not active. Please contact an administrator.']; }
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        getDb()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([password_hash($password, PASSWORD_DEFAULT), $user['id']]);
    }
    getDb()->prepare('UPDATE users SET last_login=NOW() WHERE id=?')->execute([$user['id']]);
    recordLoginAttempt((int)$user['id'], true); loginUser($user);
    if ($remember) { issueRememberToken((int)$user['id']); }
    logActivity('Login', 'Authenticated successfully.');
    return [true, 'Welcome back.'];
}

function issueRememberToken(int $userId): void
{
    $token = bin2hex(random_bytes(32));
    getDb()->prepare('UPDATE users SET remember_token=? WHERE id=?')->execute([hash('sha256', $token), $userId]);
    setcookie('transitops_remember', $token, ['expires'=>time()+60*60*24*30, 'path'=>'/', 'secure'=>!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off', 'httponly'=>true, 'samesite'=>'Lax']);
}

function attemptRememberedLogin(): void
{
    if (currentUser() || empty($_COOKIE['transitops_remember'])) { return; }
    $statement = getDb()->prepare('SELECT id, full_name, email, password_hash, role, status, phone, avatar_path FROM users WHERE remember_token=? AND status="Active" AND deleted_at IS NULL LIMIT 1');
    $statement->execute([hash('sha256', (string)$_COOKIE['transitops_remember'])]); $user=$statement->fetch();
    if ($user) { loginUser($user); issueRememberToken((int)$user['id']); recordLoginAttempt((int)$user['id'], true); }
    else { setcookie('transitops_remember','',['expires'=>time()-3600,'path'=>'/']); }
}
