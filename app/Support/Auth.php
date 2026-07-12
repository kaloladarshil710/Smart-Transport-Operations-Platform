<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Database;
use App\Core\View;

final class Auth
{
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function user(): ?array
    {
        self::startSession();

        return $_SESSION['user'] ?? null;
    }

    public static function login(array $user): void
    {
        self::startSession();
        session_regenerate_id(true);
        $_SESSION['user'] = $user;
    }

    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function requireLogin(): void
    {
        self::startSession();
        if (!isset($_SESSION['user'])) {
            header('Location: /login');
            exit;
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        $user = self::user();
        if ($user === null || !in_array($user['role'] ?? '', $roles, true)) {
            http_response_code(403);
            echo View::render('errors.403');
            exit;
        }
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    public static function csrfToken(): string
    {
        self::startSession();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validateCsrf(): bool
    {
        self::startSession();
        $token = $_POST['csrf_token'] ?? '';

        return hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function isAllowed(string $permission): bool
    {
        $user = self::user();
        if ($user === null) {
            return false;
        }

        return in_array($permission, $user['permissions'] ?? [], true);
    }

    public static function loginUser(string $email, string $password): ?array
    {
        $sql = 'SELECT u.id, CONCAT(u.first_name, " ", u.last_name) AS full_name, u.email, u.password AS password_hash, r.name AS role FROM users u LEFT JOIN user_roles ur ON ur.user_id = u.id LEFT JOIN roles r ON r.id = ur.role_id WHERE u.email = ? LIMIT 1';
        $row = Database::fetchOne($sql, [$email]);
        if ($row === null) {
            return null;
        }

        if (!self::verifyPassword($password, $row['password_hash'])) {
            return null;
        }

        $permissions = [];
        $permRows = Database::fetchAll('SELECT p.name FROM permissions p JOIN role_permissions rp ON rp.permission_id = p.id JOIN roles r ON r.id = rp.role_id WHERE r.name = ?', [$row['role']]);
        foreach ($permRows as $perm) {
            $permissions[] = $perm['name'];
        }

        return [
            'id' => (int) $row['id'],
            'name' => $row['full_name'],
            'email' => $row['email'],
            'role' => $row['role'],
            'permissions' => $permissions,
        ];
    }
}
