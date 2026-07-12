<?php

declare(strict_types=1);

namespace App\Core;

use Closure;

final class Router
{
    /** @var array<string, array<string, Closure>> */
    private array $routes = [];

    public function get(string $path, Closure $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    public function add(string $method, string $path, Closure $handler): void
    {
        $normalizedPath = '/' . trim($path, '/');
        $this->routes[strtoupper($method)][$normalizedPath] = $handler;
    }

    public function dispatch(string $method, string $uri): Response
    {
        $path = '/' . trim((string) parse_url($uri, PHP_URL_PATH), '/');
        $handler = $this->routes[strtoupper($method)][$path] ?? null;

        if ($handler === null) {
            return Response::json(['error' => 'Not found'], 404);
        }

        $response = $handler();

        return $response instanceof Response ? $response : Response::html((string) $response);
    }
}
