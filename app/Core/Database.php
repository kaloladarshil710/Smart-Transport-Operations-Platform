<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOException;
use PDOStatement;

final class Database
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $config = require dirname(__DIR__, 2) . '/config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset']
        );

        try {
            $pdo = new PDO($dsn, $config['username'], $config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage(), 0, $e);
        }

        self::$pdo = $pdo;

        return $pdo;
    }

    /** @param array<int|string, mixed> $params */
    public static function fetchAll(string $sql, array $params = []): array
    {
        $statement = self::prepare($sql, $params);

        return $statement->fetchAll();
    }

    /** @param array<int|string, mixed> $params */
    public static function fetchOne(string $sql, array $params = []): ?array
    {
        $statement = self::prepare($sql, $params);
        $row = $statement->fetch();

        return $row === false ? null : $row;
    }

    /** @param array<int|string, mixed> $params */
    public static function execute(string $sql, array $params = []): PDOStatement
    {
        return self::prepare($sql, $params);
    }

    /** @param array<int|string, mixed> $params */
    private static function prepare(string $sql, array $params = []): PDOStatement
    {
        $statement = self::connection()->prepare($sql);

        foreach ($params as $key => $value) {
            $type = PDO::PARAM_STR;

            if (is_int($value)) {
                $type = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $type = PDO::PARAM_BOOL;
            } elseif ($value === null) {
                $type = PDO::PARAM_NULL;
            }

            $statement->bindValue(is_int($key) ? $key + 1 : $key, $value, $type);
        }

        $statement->execute();

        return $statement;
    }
}
