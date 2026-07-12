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

/** Returns the current session CSRF token for forms. */
function csrfToken(): string
{
    return generateCsrfToken();
}

function verifyCsrfToken(string $token): bool
{
    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

/**
 * Enforce CSRF validation for POST requests.
 *
 * Exits via jsonResponse() on failure.
 */
function requireCsrfFromPost(): void
{
    $token = (string)($_POST['csrf_token'] ?? '');
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($token) || !verifyCsrfToken($token)) {
        jsonResponse(['ok' => false, 'message' => 'Invalid CSRF token.'], 400);
    }
}
