<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Application;
use App\Core\Response;

final class HealthController
{
    public function __construct(private readonly Application $app)
    {
    }

    public function show(): Response
    {
        return Response::json([
            'application' => $this->app->config()['name'],
            'status' => 'ok',
            'phase' => 1,
        ]);
    }
}
