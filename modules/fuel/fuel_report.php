<?php /** Fuel export/report route. */ declare(strict_types=1); require_once __DIR__.'/../../config/config.php'; requireAuth(); enforceModuleAccess('fuel'); redirect('ajax/fuel_export.php');
