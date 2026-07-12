<?php
/** JSON logout endpoint protected against cross-site requests. */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) jsonResponse(['ok'=>false],400);
logoutUser(); jsonResponse(['ok'=>true,'redirect'=>siteUrl('login.php')]);
