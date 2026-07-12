<?php
/**
 * CSRF protection helpers.
 */
declare(strict_types=1);

function generateCsrfToken(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}
