<?php

declare(strict_types=1);

use App\Controllers\HealthController;
use App\Controllers\LandingController;

$app = require __DIR__ . '/../bootstrap/app.php';

$landing = new LandingController();
$health = new HealthController($app);

$app->router()->get('/', static fn () => $landing->show());
$app->router()->get('/health', static fn () => $health->show());

$app->router()->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/')->send();
