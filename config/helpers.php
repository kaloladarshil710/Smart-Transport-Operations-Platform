<?php
/**
 * Generic helper functions for the application.
 */
declare(strict_types=1);

function e(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): void
{
    $location = $path;
    if (!preg_match('#^https?://#', $path) && !str_starts_with($path, '/')) {
        $location = BASE_URL . '/' . ltrim($path, '/');
    }

    header('Location: ' . $location);
    exit;
}

function siteUrl(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

function currentUrl(): string
{
    return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http') . '://' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . $_SERVER['REQUEST_URI'];
}

function formatCurrency(float $amount): string
{
    return '₹' . number_format($amount, 2);
}

function formatDate(?string $date): string
{
    if (empty($date)) {
        return '—';
    }

    return date('d M Y', strtotime($date));
}

function setFlash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    if (!isset($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);

    return $flash;
}

function ensureUploadDirectory(string $directory): void
{
    if (!is_dir($directory)) {
        mkdir($directory, 0755, true);
    }
}

function getDb(): PDO
{
    return $GLOBALS['pdo'];
}
