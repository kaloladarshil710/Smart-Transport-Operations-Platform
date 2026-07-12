<?php
/** JSON password-reset request endpoint. */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) jsonResponse(['ok'=>false],400);
$email=trim((string)($_POST['email'] ?? '')); if (filter_var($email,FILTER_VALIDATE_EMAIL)) createPasswordReset($email);
jsonResponse(['ok'=>true,'message'=>'If the account exists, reset instructions have been sent.']);
