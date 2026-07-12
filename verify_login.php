<?php
/** Server-side credential verification endpoint for form integrations. */
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verifyCsrfToken((string)($_POST['csrf_token'] ?? ''))) { http_response_code(400); exit('Invalid request.'); }
[$ok, $message] = authenticateCredentials(trim((string)($_POST['email'] ?? '')), (string)($_POST['password'] ?? ''), !empty($_POST['remember_me']));
if (!$ok) { setFlash('danger', $message); redirect('login.php'); }
redirect('dashboard.php');
