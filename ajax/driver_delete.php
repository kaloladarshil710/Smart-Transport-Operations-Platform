<?php
/** AJAX soft-delete endpoint. */
declare(strict_types=1);require_once __DIR__.'/../config/config.php';requireAuth();enforceModuleAccess('drivers');if($_SERVER['REQUEST_METHOD']!=='POST'||!verifyCsrfToken((string)($_POST['csrf_token']??'')))jsonResponse(['ok'=>false],400);getDb()->prepare('UPDATE drivers SET deleted_at=NOW(),status="Inactive" WHERE id=?')->execute([(int)$_POST['id']]);jsonResponse(['ok'=>true]);
