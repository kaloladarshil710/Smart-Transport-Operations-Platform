<?php
/**
 * Session hardening helpers.
 */
declare(strict_types=1);

function regenerateSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}

/** Enforces idle timeout and binds an authenticated session to its user agent. */
function enforceSessionSecurity(): void
{
    if (!isset($_SESSION['user_id'])) {
        return;
    }
    $now = time();
    $timeout = 1800;
    if (isset($_SESSION['last_activity']) && $now - (int) $_SESSION['last_activity'] > $timeout) {
        logoutUser();
        if (PHP_SAPI !== 'cli') {
            setFlash('warning', 'Your session expired after inactivity.');
            redirect('login.php');
        }
        return;
    }
    $fingerprint = hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? ''));
    if (isset($_SESSION['fingerprint']) && !hash_equals((string)$_SESSION['fingerprint'], $fingerprint)) {
        logoutUser();
        if (PHP_SAPI !== 'cli') {
            redirect('login.php');
        }
        return;
    }
    $_SESSION['last_activity'] = $now;
}
