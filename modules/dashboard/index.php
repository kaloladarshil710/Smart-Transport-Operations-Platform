<?php
/** Dashboard module entry point. */
declare(strict_types=1);
require_once __DIR__ . '/../../config/config.php';
requireAuth();
redirect('dashboard.php');
