<?php
/**
 * Trip Detail View Page
 */
declare(strict_types=1);
require_once __DIR__ . '/../../config/config.php';
requireAuth();
enforceModuleAccess('trips');
require_once __DIR__ . '/../../functions/trip_functions.php';

$tripId = (int) ($_GET['id'] ?? 0);
$trip = getTripById($tripId);
if (!$trip) { setFlash('danger', 'Trip not found.'); redirect('modules/trips/index.php'); }

$history = getTripHistory($tripId);
$pageTitle = 'Trip #' . $trip['trip_number'];
$breadcrumbs = [['label'=>'Dashboard','url'=>'dashboard.php'],['label'=>'Trips','url'=>'modules/trips/index.php'],['label'=>$trip['trip_number']]];

$statusColors = ['Draft'=>'badge-secondary','Dispatched'=>'badge-info','In Progress'=>'badge-warning','Completed'=>'badge-success','Cancelled'=>'badge-danger'];

require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/sidebar.php';
?>
<div class="main-content">
    <?php require_once __DIR__ . '/../../includes/navbar.php'; ?>
    <?php require_once __DIR__ . '/../../includes/breadcrumb.php'; ?>
    <div class="trip-view-header">
        <div class="trip-view-title"><h1><?php echo e($trip['trip_number']); ?></h1><span class="badge <?php echo e($statusColors[$trip['status']] ?? 'badge-secondary'); ?> badge-lg"><?php echo e($trip['status']); ?></span></div>
        <div class="trip-view-actions">
            <?php if ($trip['status'] === 'Draft'): ?>
            <a href="<?php echo e(siteUrl('modules/trips/edit.php?id='.$tripId)); ?>" class="btn btn-secondary"><i class="fa fa-pencil"></i> Edit</a>
            <a href="<?php echo e(siteUrl('modules/trips/dispatch.php?id='.$tripId)); ?>" class="btn btn-primary"><i class="fa fa-play"></i> Dispatch</a>
            <a href="<?php echo e(siteUrl('modules/trips/cancel.php?id='.$tripId)); ?>" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
            <?php endif; ?>
            <?php if (in_array($trip['status'],['Dispatched','In Progress'],true)): ?>
            <a href="<?php echo e(siteUrl('modules/trips/complete.php?id='.$tripId)); ?>" class="btn btn-success"><i class="fa fa-check"></i> Complete</a>
            <a href="<?php echo e(siteUrl('modules/trips/cancel.php?id='.$tripId)); ?>" class="btn btn-danger"><i class="fa fa-times"></i> Cancel</a>
            <?php endif; ?>
            <a href="<?php echo e(siteUrl('modules/trips/history.php?id='.$tripId)); ?>" class="btn btn-outline"><i class="fa fa-history"></i> Timeline</a>
            <a href="<?php echo e(siteUrl('modules/trips/index.php')); ?>" class="btn btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <div class="trip-view-grid">
        <div class="card trip-detail-card"><div class="card-header"><h3><i class="fa fa-info-circle"></i> Trip Details</h3></div>
            <div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Trip Number</span><span class="detail-value"><?php echo e($trip['trip_number']); ?></span></div>
                <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><span class="badge <?php echo e($statusColors[$trip['status']]??''); ?>"><?php echo e($trip['status']); ?></span></span></div>
                <div class="detail-item"><span class="detail-label">Start Date</span><span class="detail-value"><?php echo e(date('d M Y',strtotime($trip['start_date']))); ?></span></div>
                <div class="detail-item"><span class="detail-label">End Date</span><span class="detail-value"><?php echo e($trip['end_date']?date('d M Y',strtotime($trip['end_date'])):'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">Origin</span><span class="detail-value"><?php echo e($trip['origin']); ?></span></div>
                <div class="detail-item"><span class="detail-label">Destination</span><span class="detail-value"><?php echo e($trip['destination']); ?></span></div>
                <div class="detail-item"><span class="detail-label">Cargo Type</span><span class="detail-value"><?php echo e($trip['cargo_type']??'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">Cargo Weight</span><span class="detail-value"><?php echo e(number_format((float)$trip['cargo_weight_kg'])); ?> kg</span></div>
                <div class="detail-item"><span class="detail-label">Planned Distance</span><span class="detail-value"><?php echo e(number_format((float)$trip['planned_distance_km'],1)); ?> km</span></div>
                <div class="detail-item"><span class="detail-label">Actual Distance</span><span class="detail-value"><?php echo e($trip['actual_distance_km']?number_format((float)$trip['actual_distance_km'],1).' km':'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">Dispatch Time</span><span class="detail-value"><?php echo e($trip['dispatch_at']?date('d M Y H:i',strtotime($trip['dispatch_at'])):'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">Arrival Time</span><span class="detail-value"><?php echo e($trip['arrival_at']?date('d M Y H:i',strtotime($trip['arrival_at'])):'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">Revenue</span><span class="detail-value revenue-positive"><?php echo e(formatCurrency((float)$trip['revenue'])); ?></span></div>
                <div class="detail-item"><span class="detail-label">Fuel Used</span><span class="detail-value"><?php echo e($trip['fuel_used_liters']?number_format((float)$trip['fuel_used_liters'],1).' L':'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">Mileage</span><span class="detail-value"><?php echo e($trip['mileage_kmpl']?number_format((float)$trip['mileage_kmpl'],2).' km/L':'—'); ?></span></div>
            </div>
            <?php if (!empty($trip['remarks'])): ?><div class="detail-remarks"><strong>Remarks:</strong><p><?php echo e($trip['remarks']); ?></p></div><?php endif; ?>
        </div>
        <div class="card trip-detail-card"><div class="card-header"><h3><i class="fa fa-bus"></i> Vehicle</h3></div>
            <div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Registration</span><span class="detail-value"><?php echo e($trip['registration_number']); ?></span></div>
                <div class="detail-item"><span class="detail-label">Name</span><span class="detail-value"><?php echo e($trip['vehicle_name']??'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">Capacity</span><span class="detail-value"><?php echo e(number_format((int)$trip['capacity_kg'])); ?> kg</span></div>
                <div class="detail-item"><span class="detail-label">Fuel Eff.</span><span class="detail-value"><?php echo e(number_format((float)$trip['fuel_efficiency'],2)); ?> km/L</span></div>
                <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><span class="badge <?php echo e($trip['vehicle_status']==='Available'?'badge-success':'badge-warning'); ?>"><?php echo e($trip['vehicle_status']); ?></span></span></div>
            </div>
            <a href="<?php echo e(siteUrl('modules/vehicles/index.php?id='.$trip['vehicle_id'])); ?>" class="card-link">View Vehicle <i class="fa fa-arrow-right"></i></a>
        </div>
        <div class="card trip-detail-card"><div class="card-header"><h3><i class="fa fa-user"></i> Driver</h3></div>
            <div class="detail-grid">
                <div class="detail-item"><span class="detail-label">Name</span><span class="detail-value"><?php echo e($trip['driver_name']); ?></span></div>
                <div class="detail-item"><span class="detail-label">Phone</span><span class="detail-value"><?php echo e($trip['driver_phone']??'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">License</span><span class="detail-value"><?php echo e($trip['license_number']??'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">License Exp.</span><span class="detail-value"><?php echo e($trip['license_expiry']?date('d M Y',strtotime($trip['license_expiry'])):'—'); ?></span></div>
                <div class="detail-item"><span class="detail-label">Status</span><span class="detail-value"><span class="badge <?php echo e($trip['driver_status']==='Available'?'badge-success':'badge-warning'); ?>"><?php echo e($trip['driver_status']); ?></span></span></div>
                <div class="detail-item"><span class="detail-label">Safety</span><span class="detail-value"><?php echo e(number_format((float)$trip['safety_score'],1)); ?>%</span></div>
            </div>
            <a href="<?php echo e(siteUrl('modules/drivers/view.php?id='.$trip['driver_id'])); ?>" class="card-link">View Driver <i class="fa fa-arrow-right"></i></a>
        </div>
    </div>
    <div class="card timeline-card"><div class="card-header"><h3><i class="fa fa-clock-o"></i> Trip Timeline</h3></div>
        <div class="timeline" style="padding:16px 20px;">
            <?php if (empty($history)): ?><div class="empty-state">No timeline events recorded.</div>
            <?php else: foreach ($history as $event): ?>
            <div class="timeline-item" style="display:flex;gap:12px;padding:8px 0;border-bottom:1px solid var(--border);">
                <div class="timeline-marker" style="width:30px;text-align:center;"><i class="fa <?php echo match($event['status']){'Draft'=>'fa-file-o','Dispatched'=>'fa-play','Completed'=>'fa-check','Cancelled'=>'fa-times',default=>'fa-circle'}; ?>"></i></div>
                <div class="timeline-content" style="flex:1;"><strong><?php echo e($event['status']); ?></strong> — <?php echo e($event['note']??''); ?> <small style="color:var(--muted);float:right;"><?php echo e(date('d M Y H:i',strtotime($event['created_at']))); ?></small></div>
            </div>
            <?php endforeach; endif; ?>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
