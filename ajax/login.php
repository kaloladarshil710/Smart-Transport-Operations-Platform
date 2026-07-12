<?php
/** JSON login endpoint for progressive AJAX enhancement. */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) jsonResponse(['ok'=>false,'message'=>'Invalid request.'],400);
[$ok,$message]=authenticateCredentials(trim((string)($_POST['email'] ?? '')),(string)($_POST['password'] ?? ''),!empty($_POST['remember_me']));
jsonResponse(['ok'=>$ok,'message'=>$message,'redirect'=>$ok ? siteUrl('dashboard.php') : null],$ok ? 200 : 422);
