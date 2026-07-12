<?php
/** Explicit remember-me bootstrap for environments that route this separately. */
declare(strict_types=1);
require_once __DIR__ . '/config/config.php';
attemptRememberedLogin();
redirect(currentUser() ? 'dashboard.php' : 'login.php');
