<?php
/** Vehicle CSV export for authorized vehicle-management users. */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/functions/vehicle_functions.php';
requireAuth();
enforceModuleAccess('vehicles');

$filters = [
    'status' => trim((string) ($_GET['status'] ?? '')),
    'region_id' => isset($_GET['region_id']) ? (int) $_GET['region_id'] : null,
    'purchase_year' => isset($_GET['purchase_year']) ? (int) $_GET['purchase_year'] : null,
];

exportVehiclesCsv($filters);
