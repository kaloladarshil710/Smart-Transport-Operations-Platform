<?php

declare(strict_types=1);

use App\Core\Application;

require __DIR__ . '/../app/Core/Autoloader.php';

App\Core\Autoloader::register(dirname(__DIR__) . '/app');
App\Core\Environment::load(dirname(__DIR__) . '/.env');

$config = require __DIR__ . '/../config/app.php';
date_default_timezone_set($config['timezone']);

return new Application($config, dirname(__DIR__));
