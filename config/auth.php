<?php
/**
 * Authentication helpers.
 */
declare(strict_types=1);

function loginUser(array $user): void
{
    $_SESSION['user'] = $user;
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'] ?? 'operator';
}

function logoutUser(): void
{
    session_unset();
    session_destroy();
}

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
