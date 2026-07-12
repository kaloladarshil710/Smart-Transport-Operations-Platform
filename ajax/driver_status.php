<?php
/** AJAX driver availability endpoint. */
declare(strict_types=1);require_once __DIR__.'/../config/config.php';require_once ROOT_PATH.'/functions/driver_functions.php';requireAuth();enforceModuleAccess('drivers');if($_SERVER['REQUEST_METHOD']!=='POST'||!verifyCsrfToken((string)($_POST['csrf_token']??'')))jsonResponse(['ok'=>false],400);[$ok,$message]=updateDriverStatus((int)$_POST['id'],(string)$_POST['status']);jsonResponse(['ok'=>$ok,'message'=>$message],$ok?200:422);
