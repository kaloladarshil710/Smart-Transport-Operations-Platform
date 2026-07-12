<?php

declare(strict_types=1);

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\DriverController;
use App\Controllers\HealthController;
use App\Controllers\LandingController;
use App\Controllers\ReportController;
use App\Controllers\TripController;
use App\Controllers\VehicleController;

$app = require __DIR__ . '/../bootstrap/app.php';

$landing = new LandingController();
$health = new HealthController($app);
$auth = new AuthController();
$dashboard = new DashboardController();
$vehicles = new VehicleController();
$drivers = new DriverController();
$trips = new TripController();
$reports = new ReportController();

$app->router()->get('/', static fn () => $landing->show());
$app->router()->get('/health', static fn () => $health->show());
$app->router()->get('/login', static fn () => $auth->showLogin());
$app->router()->post('/login', static fn () => $auth->login());
$app->router()->get('/logout', static fn () => $auth->logout());
$app->router()->get('/dashboard', static fn () => $dashboard->show());
$app->router()->get('/vehicles', static fn () => $vehicles->index());
$app->router()->get('/vehicles/create', static fn () => $vehicles->create());
$app->router()->post('/vehicles', static fn () => $vehicles->store());
$app->router()->get('/drivers', static fn () => $drivers->index());
$app->router()->get('/drivers/create', static fn () => $drivers->create());
$app->router()->post('/drivers', static fn () => $drivers->store());
$app->router()->get('/trips', static fn () => $trips->index());
$app->router()->get('/trips/create', static fn () => $trips->create());
$app->router()->post('/trips', static fn () => $trips->store());
$app->router()->get('/reports', static fn () => $reports->index());

$app->router()->dispatch($_SERVER['REQUEST_METHOD'] ?? 'GET', $_SERVER['REQUEST_URI'] ?? '/')->send();
