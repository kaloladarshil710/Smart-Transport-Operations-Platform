<?php
/** Maintenance CSV export for authorized maintenance users. */
declare(strict_types=1);
require_once __DIR__ . '/../config/config.php';
require_once ROOT_PATH . '/functions/maintenance_functions.php';
requireAuth();
enforceModuleAccess('maintenance');

$status = trim((string) ($_GET['status'] ?? ''));
$where = 'm.deleted_at IS NULL';
$params = [];

if ($status !== '') {
    $where .= ' AND m.status = ?';
    $params[] = $status;
}

$sql = "SELECT m.maintenance_code, v.registration_number, m.maintenance_type, m.priority, m.vendor_name, m.mechanic_name, m.cost, m.scheduled_date, m.status
        FROM maintenance_logs m
        JOIN vehicles v ON v.id = m.vehicle_id
        WHERE {$where}
        ORDER BY m.scheduled_date DESC, m.id DESC";

$stmt = getDb()->prepare($sql);
$stmt->execute($params);

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="transitops-maintenance.csv"');

$out = fopen('php://output', 'w');
fputcsv($out, ['Maintenance Code', 'Vehicle', 'Type', 'Priority', 'Vendor', 'Mechanic', 'Cost', 'Scheduled', 'Status']);

while ($row = $stmt->fetch()) {
    fputcsv($out, [
        $row['maintenance_code'] ?? '',
        $row['registration_number'] ?? '',
        $row['maintenance_type'] ?? '',
        $row['priority'] ?? '',
        $row['vendor_name'] ?? '',
        $row['mechanic_name'] ?? '',
        $row['cost'] ?? '',
        $row['scheduled_date'] ?? '',
        $row['status'] ?? '',
    ]);
}

fclose($out);
exit;
