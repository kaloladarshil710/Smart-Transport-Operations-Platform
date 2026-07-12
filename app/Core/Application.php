<?php

declare(strict_types=1);

namespace App\Core;

final class Application
{
    private Router $router;

    /** @param array<string, mixed> $config */
    public function __construct(private readonly array $config, private readonly string $basePath)
    {
        $this->router = new Router();
    }

    public function router(): Router
    {
        return $this->router;
    }

    /** @return array<string, mixed> */
    public function config(): array
    {
        return $this->config;
    }

    public function basePath(): string
    {
        return $this->basePath;
    }
}
