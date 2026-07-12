<?php
/** AJAX create/update driver endpoint. */
declare(strict_types=1);require_once __DIR__.'/../config/config.php';require_once ROOT_PATH.'/functions/driver_functions.php';requireAuth();enforceModuleAccess('drivers');if($_SERVER['REQUEST_METHOD']!=='POST'||!verifyCsrfToken((string)($_POST['csrf_token']??'')))jsonResponse(['ok'=>false],400);[$ok,$result]=saveDriver($_POST,isset($_POST['id'])?(int)$_POST['id']:null);jsonResponse(['ok'=>$ok,'data'=>$result],$ok?200:422);
