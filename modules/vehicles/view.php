<?php
declare(strict_types=1);
require_once __DIR__ . '/../../config/config.php';
require_once ROOT_PATH . '/functions/vehicle_functions.php';
requireAuth(); enforceModuleAccess('vehicles');
$vehicle = getVehicleById((int)($_GET['id'] ?? 0));
if (!$vehicle) { setFlash('danger', 'Vehicle not found.'); redirect('modules/vehicles/index.php'); }
$tripStmt = getDb()->prepare('SELECT COUNT(*) total, COALESCE(SUM(revenue),0) revenue FROM trips WHERE vehicle_id=? AND deleted_at IS NULL');
$tripStmt->execute([(int)$vehicle['id']]); $tripSummary = $tripStmt->fetch();
$pageTitle = (string)$vehicle['registration_number']; require ROOT_PATH . '/includes/header.php'; require ROOT_PATH . '/includes/sidebar.php';
?>
<main class="main-content"><?php require ROOT_PATH . '/includes/navbar.php'; ?>
<section class="page-section d-flex justify-between align-center"><div><h1 class="page-title"><?= e($vehicle['registration_number']) ?></h1><p class="page-subtitle"><?= e((string)($vehicle['vehicle_name'] ?: $vehicle['make'].' '.$vehicle['model'])) ?> · <?= e($vehicle['region_name']) ?></p></div><div class="form-actions"><a class="btn btn-primary" href="edit.php?id=<?= (int)$vehicle['id'] ?>">Edit vehicle</a><a class="btn btn-secondary" href="documents.php?id=<?= (int)$vehicle['id'] ?>">Documents</a></div></section>
<section class="grid grid-4 page-section"><?php foreach(['Status'=>$vehicle['status'],'Trips'=>$tripSummary['total'],'Revenue'=>formatCurrency((float)$tripSummary['revenue']),'Odometer'=>number_format((float)$vehicle['odometer_km']).' km'] as $label=>$value): ?><article class="card dashboard-card"><small><?= e($label) ?></small><h2><?= e((string)$value) ?></h2></article><?php endforeach; ?></section>
<section class="grid grid-2"><article class="card dashboard-card"><h2>Fleet details</h2><dl class="profile-list"><dt>Make / model</dt><dd><?= e($vehicle['make'].' '.$vehicle['model']) ?></dd><dt>Type</dt><dd><?= e($vehicle['vehicle_type_name']) ?></dd><dt>Capacity</dt><dd><?= e((string)$vehicle['capacity_kg']) ?> kg</dd><dt>Fuel</dt><dd><?= e((string)$vehicle['fuel_type']) ?></dd><dt>Year</dt><dd><?= e((string)$vehicle['year']) ?></dd></dl></article><article class="card dashboard-card"><h2>Compliance</h2><dl class="profile-list"><dt>Insurance</dt><dd><?= e(formatDate($vehicle['insurance_expiry'])) ?></dd><dt>Fitness</dt><dd><?= e(formatDate($vehicle['fitness_expiry'])) ?></dd><dt>Permit</dt><dd><?= e(formatDate($vehicle['permit_expiry'])) ?></dd><dt>Pollution</dt><dd><?= e(formatDate($vehicle['pollution_expiry'])) ?></dd></dl></article></section>
<nav class="profile-tabs"><a href="status.php?id=<?= (int)$vehicle['id'] ?>">Change status</a><a href="documents.php?id=<?= (int)$vehicle['id'] ?>">Documents</a><a href="delete.php?id=<?= (int)$vehicle['id'] ?>">Archive vehicle</a></nav>
</main><?php require ROOT_PATH . '/includes/footer.php'; ?>
