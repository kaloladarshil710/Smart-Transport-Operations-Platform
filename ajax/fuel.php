<?php
/** JSON fuel transaction endpoint. */
declare(strict_types=1);require_once __DIR__.'/../config/config.php';require_once ROOT_PATH.'/functions/fuel_functions.php';requireAuth();enforceModuleAccess('fuel');if($_SERVER['REQUEST_METHOD']!=='POST'||!verifyCsrfToken((string)($_POST['csrf_token']??'')))jsonResponse(['ok'=>false],400);[$ok,$data]=saveFuelLog($_POST,isset($_POST['id'])?(int)$_POST['id']:null);jsonResponse(['ok'=>$ok,'data'=>$data],$ok?200:422);
