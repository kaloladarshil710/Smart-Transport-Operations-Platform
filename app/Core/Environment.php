<?php

declare(strict_types=1);

namespace App\Core;

final class Environment
{
    public static function load(string $file): void
    {
        if (!is_file($file) || !is_readable($file)) {
            return;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if ($lines === false) {
            throw new \RuntimeException('Unable to read environment configuration.');
        }

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            if ($key === '' || getenv($key) !== false) {
                continue;
            }

            if (strlen($value) >= 2 && $value[0] === '"' && $value[strlen($value) - 1] === '"') {
                $value = stripcslashes(substr($value, 1, -1));
            }

            putenv($key . '=' . $value);
            $_ENV[$key] = $value;
        }
    }
}
