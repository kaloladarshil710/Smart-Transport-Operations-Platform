<?php
/**
 * Authentication helpers.
 */
declare(strict_types=1);

function loginUser(array $user): void
{
    regenerateSession();
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'] ?? 'operator';
    $_SESSION['last_activity'] = time();
    $_SESSION['fingerprint'] = hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
}

function logoutUser(): void
{
    $userId = (int)($_SESSION['user_id'] ?? 0);
    if ($userId > 0 && isset($GLOBALS['pdo'])) {
        getDb()->prepare('UPDATE users SET remember_token = NULL WHERE id = ?')->execute([$userId]);
        getDb()->prepare('UPDATE login_logs SET logout_at = NOW() WHERE user_id = ? AND logout_at IS NULL')->execute([$userId]);
    }
    setcookie('transitops_remember', '', ['expires' => time() - 3600, 'path' => '/', 'httponly' => true, 'samesite' => 'Lax', 'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off']);
    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) { session_destroy(); }
}

require_once ROOT_PATH . '/functions/auth_functions.php';
require_once ROOT_PATH . '/functions/password_functions.php';

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireAuth(): void
{
    if (!currentUser()) {
        setFlash('danger', 'Please sign in to continue.');
        redirect('login.php');
    }
}

function isAdmin(): bool
{
    return (currentUser()['role'] ?? '') === 'admin';
}
