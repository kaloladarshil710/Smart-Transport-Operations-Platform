<?php
/** Driver lifecycle, performance, documents and availability services. */
declare(strict_types=1);
require_once __DIR__ . '/driver_validation.php';

function driverFieldIsUnique(string $field, string $value, ?int $excludeId=null): bool
{
    if (!in_array($field,['email','phone','license_number'],true) || $value==='') return true;
    $sql="SELECT 1 FROM drivers WHERE {$field}=? AND deleted_at IS NULL"; $params=[$value]; if ($excludeId) {$sql.=' AND id<>?';$params[]=$excludeId;} $sql.=' LIMIT 1';
    $statement=getDb()->prepare($sql);$statement->execute($params);return !$statement->fetch();
}
function getDriverById(int $id): ?array
{
    $statement=getDb()->prepare('SELECT d.*,r.name region_name,(SELECT v.registration_number FROM vehicles v WHERE v.assigned_driver_id=d.id AND v.deleted_at IS NULL LIMIT 1) current_vehicle FROM drivers d JOIN regions r ON r.id=d.region_id WHERE d.id=? AND d.deleted_at IS NULL LIMIT 1');
    $statement->execute([$id]);$driver=$statement->fetch();return $driver?:null;
}
function driverLicenseStatus(string $expiry): string
{
    $days=(int)floor((strtotime($expiry)-strtotime('today'))/86400); return $days<0?'Expired':($days<=30?'Expiring Soon':'Valid');
}
function driverAvailability(array $driver): string
{
    if (($driver['status'] ?? '')==='Suspended') return 'Suspended'; if (($driver['status'] ?? '')==='On Trip') return 'On Trip'; if (($driver['status'] ?? '')==='Off Duty') return 'Off Duty'; if (($driver['status'] ?? '')==='Inactive') return 'Inactive'; return driverLicenseStatus((string)$driver['license_expiry'])==='Expired'?'Unavailable':'Available';
}
function saveDriver(array $input, ?int $id=null): array
{
    $errors=validateDriverInput($input,$id); if ($errors) return [false,$errors];
    $fields=['full_name','email','phone','license_expiry','region_id','status','employee_id','date_of_birth','gender','blood_group','address','city','state_name','country_name','postal_code','emergency_contact_name','emergency_contact','joining_date','experience_years','license_category','license_issue_date','medical_certificate_expiry','police_verification_date','aadhaar_number','pan_number','safety_score','remarks'];
    $values=[]; foreach ($fields as $field) $values[$field]=trim((string)($input[$field] ?? '')) ?: null;
    $values['license_number']=trim((string)$input['license_number']);
    if ($id) { unset($values['employee_id']); if (!empty($input['lock_license'])) unset($values['license_number']); $sets=[]; foreach ($values as $field=>$value) {$sets[]="$field=?";} $statement=getDb()->prepare('UPDATE drivers SET '.implode(',',$sets).' WHERE id=?');$statement->execute([...array_values($values),$id]); logActivity('Driver updated','Driver #'.$id); return [true,$id]; }
    $values['uuid']=bin2hex(random_bytes(16)); $columns=array_keys($values);$statement=getDb()->prepare('INSERT INTO drivers ('.implode(',',$columns).') VALUES ('.implode(',',array_fill(0,count($columns),'?')).')');$statement->execute(array_values($values));$newId=(int)getDb()->lastInsertId();logActivity('Driver created','Driver #'.$newId);return [true,$newId];
}
function updateDriverStatus(int $id,string $status): array
{
    if (!in_array($status,['Available','Off Duty','Suspended','Inactive'],true)) return [false,'Invalid driver status.']; $driver=getDriverById($id);if (!$driver)return [false,'Driver not found.']; if ($driver['status']==='On Trip') return [false,'A driver on an active trip cannot be changed manually.'];
    getDb()->prepare('UPDATE drivers SET status=? WHERE id=?')->execute([$status,$id]); logActivity('Driver status changed',"Driver #$id set to $status");return [true,'Driver status updated.'];
}
function driverPerformance(int $id): array
{
    $statement=getDb()->prepare("SELECT COUNT(*) total_trips,SUM(status='Completed') completed_trips,SUM(status='Cancelled') cancelled_trips,COALESCE(AVG(actual_distance_km),0) average_distance,COALESCE(SUM(revenue),0) revenue_generated FROM trips WHERE driver_id=? AND deleted_at IS NULL");$statement->execute([$id]);$stats=$statement->fetch() ?: [];
    $fuel=getDb()->prepare('SELECT COALESCE(SUM(t.actual_distance_km)/NULLIF(SUM(f.quantity_liters),0),0) fuel_efficiency FROM trips t LEFT JOIN fuel_logs f ON f.trip_id=t.id WHERE t.driver_id=?');$fuel->execute([$id]);$stats['fuel_efficiency']=$fuel->fetchColumn() ?: 0;return $stats;
}
