<?php
/**
 * Application-level helpers for activity logging and safe identifiers.
 */
declare(strict_types=1);

function logActivity(string $action, ?string $description = null): void
{
    if (!isset($GLOBALS['pdo']) || !currentUser()) {
        return;
    }
    $statement = getDb()->prepare('INSERT INTO activity_logs (user_id, action, description) VALUES (?, ?, ?)');
    $statement->execute([(int) currentUser()['id'], $action, $description]);
}

function safeReturnPath(string $fallback = 'dashboard.php'): string
{
    $path = (string)($_POST['return_to'] ?? $_GET['return_to'] ?? $fallback);
    return str_starts_with($path, '/') || str_contains($path, '://') ? $fallback : $path;
}
