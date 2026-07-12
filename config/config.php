<?php
/**
 * Application configuration.
 *
 * @package TransitOps
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_name('transitops_session');
    session_set_cookie_params(['lifetime' => 0, 'path' => '/', 'secure' => $isHttps, 'httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

date_default_timezone_set('Asia/Kolkata');

if (!defined('APP_NAME')) {
    define('APP_NAME', 'TransitOps');
}
if (!defined('APP_VERSION')) {
    define('APP_VERSION', '1.0.0');
}
if (!defined('ROOT_PATH')) {
    define('ROOT_PATH', dirname(__DIR__));
}
if (!defined('BASE_URL')) {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $appFolder = basename(rtrim(ROOT_PATH, DIRECTORY_SEPARATOR));
    $basePath = '/' . ltrim($appFolder, '/');
    define('BASE_URL', rtrim($protocol . $host . $basePath, '/'));
}
if (!defined('UPLOAD_PATH')) {
    define('UPLOAD_PATH', ROOT_PATH . DIRECTORY_SEPARATOR . 'uploads');
}
if (!defined('ASSET_URL')) {
    define('ASSET_URL', BASE_URL . '/assets');
}

require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/session.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/roles.php';
require_once __DIR__ . '/permissions.php';
require_once __DIR__ . '/middleware.php';
require_once __DIR__ . '/validation.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/error_handler.php';
require_once __DIR__ . '/functions.php';

$__db = null;
try {
    $__db = connectDatabase();
} catch (Throwable $exception) {
    $__db = null;
}

if ($__db instanceof PDO) {
    $GLOBALS['pdo'] = $__db;
    attemptRememberedLogin();
}

enforceSessionSecurity();
