<?php
/** Manual session-timeout endpoint used by client-side idle policies. */
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
logoutUser(); setFlash('warning','Your session has expired. Please sign in again.'); redirect('login.php');
