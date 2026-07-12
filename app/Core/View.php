<?php

declare(strict_types=1);

namespace App\Core;

final class View
{
    /** @param array<string, mixed> $data */
    public static function render(string $view, array $data = []): string
    {
        $file = dirname(__DIR__, 2) . '/resources/views/' . str_replace('.', '/', $view) . '.php';

        if (!is_file($file)) {
            throw new \RuntimeException(sprintf('View "%s" was not found.', $view));
        }

        extract($data, EXTR_SKIP);
        ob_start();
        require $file;

        return (string) ob_get_clean();
    }

    public static function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
