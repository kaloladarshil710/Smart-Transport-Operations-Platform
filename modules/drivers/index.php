<?php
/** Enterprise driver directory with secure filters, sorting and pagination. */
declare(strict_types=1);
require_once __DIR__ . '/../../config/config.php'; require_once ROOT_PATH . '/functions/driver_functions.php'; requireAuth(); enforceModuleAccess('drivers');
$limitInput = filter_input(INPUT_GET, 'limit', FILTER_VALIDATE_INT);
$limit = in_array($limitInput, [10, 25, 50, 100], true) ? (int) $limitInput : 10;
$page = max(1, (int) ($_GET['page'] ?? 1));
$offset = ($page - 1) * $limit;
$q = trim((string) ($_GET['q'] ?? ''));
$status = trim((string) ($_GET['status'] ?? ''));
$sort = (string) ($_GET['sort'] ?? 'newest');
$order = ['newest' => 'd.id DESC', 'oldest' => 'd.id ASC', 'name' => 'd.full_name ASC', 'safety' => 'd.safety_score DESC', 'experience' => 'd.experience_years DESC', 'license' => 'd.license_expiry ASC'][$sort] ?? 'd.id DESC';
$where = ['d.deleted_at IS NULL'];
$params = [];
if ($q !== '') {
    $where[] = '(d.full_name LIKE ? OR d.employee_id LIKE ? OR d.license_number LIKE ? OR d.phone LIKE ? OR d.email LIKE ?)';
    for ($i = 0; $i < 5; $i++) {
        $params[] = '%' . $q . '%';
    }
}
if ($status !== '') {
    $where[] = 'd.status=?';
    $params[] = $status;
}
$clause = implode(' AND ', $where);
$count=getDb()->prepare("SELECT COUNT(*) FROM drivers d WHERE $clause");$count->execute($params);$total=(int)$count->fetchColumn();$statement=getDb()->prepare("SELECT d.*,r.name region_name,(SELECT t.trip_number FROM trips t WHERE t.driver_id=d.id AND t.status='Dispatched' ORDER BY t.id DESC LIMIT 1) current_trip FROM drivers d JOIN regions r ON r.id=d.region_id WHERE $clause ORDER BY $order LIMIT ? OFFSET ?");foreach($params as $i=>$value)$statement->bindValue($i+1,$value);$statement->bindValue(count($params)+1,$limit,PDO::PARAM_INT);$statement->bindValue(count($params)+2,$offset,PDO::PARAM_INT);$statement->execute();$drivers=$statement->fetchAll();$totalPages=max(1,(int)ceil($total/$limit));
$pageTitle='Drivers';require ROOT_PATH.'/includes/header.php';require ROOT_PATH.'/includes/sidebar.php';
?>
<main class="main-content driver-module"><?php require ROOT_PATH.'/includes/navbar.php'; ?><section class="page-heading page-section"><div><h1 class="page-title">Driver management</h1><p class="page-subtitle">Manage compliance, availability, safety and operational performance.</p></div><div class="d-flex gap-2"><a class="btn btn-secondary" href="license.php"><i class="fa fa-id-card"></i> License tracking</a><a class="btn btn-primary" href="add.php"><i class="fa fa-plus"></i> Add driver</a></div></section>
<section class="card table-card"><form class="driver-toolbar" method="get"><?php require ROOT_PATH.'/includes/searchbar.php'; ?><select name="status"><option value="">All statuses</option><?php foreach(['Available','On Trip','Off Duty','Suspended','Inactive'] as $item):?><option<?= $status===$item?' selected':'' ?>><?=e($item)?></option><?php endforeach;?></select><select name="sort"><option value="newest">Newest</option><option value="name">Alphabetical</option><option value="safety">Safety score</option><option value="experience">Experience</option><option value="license">License expiry</option></select><select name="limit"><?php foreach([10,25,50,100] as $item):?><option<?= $limit===$item?' selected':'' ?>><?=$item?></option><?php endforeach;?></select><button class="btn btn-secondary">Apply</button><a class="btn btn-outline" href="<?=e(siteUrl('ajax/driver_export.php'))?>">CSV export</a></form><div class="table-responsive"><table class="driver-table"><thead><tr><th>Driver</th><th>License</th><th>Contact</th><th>Experience</th><th>Safety</th><th>Availability</th><th>License status</th><th>Current trip</th><th></th></tr></thead><tbody><?php foreach($drivers as $driver):$license=driverLicenseStatus($driver['license_expiry']);$availability=driverAvailability($driver);?><tr><td><div class="driver-identity"><span class="driver-avatar"><?=e(strtoupper(substr($driver['full_name'],0,1)))?></span><span><strong><?=e($driver['full_name'])?></strong><small><?=e($driver['employee_id']?:'No employee ID')?></small></span></div></td><td><?=e($driver['license_number'])?><small><?=e($driver['license_category']?:'—')?></small></td><td><?=e($driver['phone'])?><small><?=e($driver['email'])?></small></td><td><?=e((string)$driver['experience_years'])?> yrs</td><td><span class="safety-score"><?=e((string)$driver['safety_score'])?></span></td><td><span class="badge badge-<?= $availability==='Available'?'success':($availability==='On Trip'?'info':($availability==='Suspended'?'danger':'warning'))?>"><?=e($availability)?></span></td><td><span class="badge badge-<?= $license==='Valid'?'success':($license==='Expired'?'danger':'warning')?>"><?=e($license)?></span></td><td><?=e($driver['current_trip']?:'—')?></td><td><div class="row-actions"><a href="view.php?id=<?=$driver['id']?>" aria-label="View driver"><i class="fa fa-eye"></i></a><a href="edit.php?id=<?=$driver['id']?>" aria-label="Edit driver"><i class="fa fa-pencil"></i></a><a href="documents.php?id=<?=$driver['id']?>" aria-label="Driver documents"><i class="fa fa-folder-open"></i></a></div></td></tr><?php endforeach;?><?php if(!$drivers):?><tr><td colspan="9" class="empty-state">No drivers match the selected filters.</td></tr><?php endif;?></tbody></table></div><?php $paginationUrl='?q='.urlencode($q).'&status='.urlencode($status).'&sort='.urlencode($sort).'&limit='.$limit.'&page=';require ROOT_PATH.'/includes/pagination.php';?></section></main><?php require ROOT_PATH.'/includes/footer.php'; ?>
