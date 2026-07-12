<?php
/**
 * Database bootstrap for MySQL using PDO.
 */
declare(strict_types=1);

function connectDatabase(): PDO
{
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $dbName = getenv('DB_NAME') ?: 'transitops';
    $user = getenv('DB_USER') ?: 'root';
    $password = getenv('DB_PASS') ?: '';

    $dsn = 'mysql:host=' . $host . ';dbname=' . $dbName . ';charset=utf8mb4';

    return new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
}
