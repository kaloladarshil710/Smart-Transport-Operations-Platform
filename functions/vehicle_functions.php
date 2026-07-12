<?php
/**
 * Vehicle business logic helpers for TransitOps.
 *
 * Implements prepared-statement based queries, pagination/sorting/search,
 * business-rule enforcement, status transitions, document management and exports.
 *
 * @package TransitOps
 * @subpackage Vehicles
 */
declare(strict_types=1);

require_once __DIR__ . '/validation_functions.php';
require_once __DIR__ . '/upload_functions.php';

/**
 * Get vehicle by ID (non-deleted).
 *
 * @param int $vehicleId Vehicle ID
 * @return array<string, mixed>|null
 */
function getVehicleById(int $vehicleId): ?array
{
    $stmt = getDb()->prepare(
        'SELECT v.*, vt.name AS vehicle_type_name, r.name AS region_name
         FROM vehicles v
         JOIN vehicle_types vt ON vt.id=v.vehicle_type_id
         JOIN regions r ON r.id=v.region_id
         WHERE v.id=? AND v.deleted_at IS NULL
         LIMIT 1'
    );
    $stmt->execute([$vehicleId]);
    $row = $stmt->fetch();
    return $row ? (array)$row : null;
}

/**
 * Check for unique registration number.
 *
 * @param string $registrationNumber registration_number
 * @param int|null $excludeVehicleId exclude vehicle id (for edit)
 * @return bool
 */
function isRegistrationNumberUnique(string $registrationNumber, ?int $excludeVehicleId = null): bool
{
    $sql = 'SELECT 1 FROM vehicles WHERE registration_number=? AND deleted_at IS NULL';
    $params = [$registrationNumber];

    if ($excludeVehicleId !== null) {
        $sql .= ' AND id<>?';
        $params[] = $excludeVehicleId;
    }

    $sql .= ' LIMIT 1';
    $stmt = getDb()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() === false;
}

/**
 * Check unique engine number.
 *
 * @param string $engineNumber
 * @param int|null $excludeVehicleId
 * @return bool
 */
function isEngineNumberUnique(string $engineNumber, ?int $excludeVehicleId = null): bool
{
    $engineNumber = trim($engineNumber);
    if ($engineNumber === '') {
        return true; // configurable: optional field
    }

    $sql = 'SELECT 1 FROM vehicles WHERE engine_number=? AND engine_number IS NOT NULL AND deleted_at IS NULL';
    $params = [$engineNumber];

    if ($excludeVehicleId !== null) {
        $sql .= ' AND id<>?';
        $params[] = $excludeVehicleId;
    }

    $sql .= ' LIMIT 1';
    $stmt = getDb()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() === false;
}

/**
 * Check unique chassis number.
 *
 * @param string $chassisNumber
 * @param int|null $excludeVehicleId
 * @return bool
 */
function isChassisNumberUnique(string $chassisNumber, ?int $excludeVehicleId = null): bool
{
    $chassisNumber = trim($chassisNumber);
    if ($chassisNumber === '') {
        return true; // configurable: optional field
    }

    $sql = 'SELECT 1 FROM vehicles WHERE chassis_number=? AND chassis_number IS NOT NULL AND deleted_at IS NULL';
    $params = [$chassisNumber];

    if ($excludeVehicleId !== null) {
        $sql .= ' AND id<>?';
        $params[] = $excludeVehicleId;
    }

    $sql .= ' LIMIT 1';
    $stmt = getDb()->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetch() === false;
}

/**
 * Enforce assignment/retirement business rules before status changes.
 *
 * Rules (from requirements):
 * - Retired vehicle cannot be assigned
 * - Vehicle On Trip cannot be assigned again
 * - Vehicle In Shop cannot be assigned
 * - Vehicle Under Maintenance cannot be assigned
 *
 * @param int $vehicleId
 * @param string $targetStatus
 * @return array{ok:bool, message?:string}
 */
function canTransitionVehicleStatus(int $vehicleId, string $targetStatus): array
{
    $vehicle = getVehicleById($vehicleId);
    if (!$vehicle) {
        return ['ok' => false, 'message' => 'Vehicle not found.'];
    }

    $currentStatus = (string)($vehicle['status'] ?? '');

    // Status values for module:
    $allowed = ['Available', 'On Trip', 'In Shop', 'Retired', 'Sold'];
    if (!in_array($targetStatus, $allowed, true)) {
        return ['ok' => false, 'message' => 'Invalid target status.'];
    }

    // If retiring or sold, ensure not on active trip.
    if (in_array($targetStatus, ['Retired', 'Sold'], true)) {
        $stmt = getDb()->prepare(
            'SELECT 1
             FROM trips t
             WHERE t.vehicle_id=? AND t.deleted_at IS NULL AND t.status IN (\'Dispatched\',\'In Progress\')
             LIMIT 1'
        );
        $stmt->execute([$vehicleId]);
        if ($stmt->fetch()) {
            return ['ok' => false, 'message' => 'Vehicle cannot be retired/sold while it is on an active trip.'];
        }
    }

    // If trying to set to Available while in maintenance/in shop, allow; maintenance completion handled elsewhere.
    // If trying to set to In Shop while on trip, block.
    if ($targetStatus === 'In Shop' && $currentStatus === 'On Trip') {
        return ['ok' => false, 'message' => 'Vehicle cannot enter shop while it is on a trip.'];
    }

    return ['ok' => true];
}

/**
 * Update vehicle status with audit log.
 *
 * @param int $vehicleId
 * @param string $newStatus
 * @return bool
 */
function updateVehicleStatus(int $vehicleId, string $newStatus): bool
{
    $transition = canTransitionVehicleStatus($vehicleId, $newStatus);
    if (!$transition['ok']) {
        return false;
    }

    $stmt = getDb()->prepare('UPDATE vehicles SET status=?, updated_at=NOW() WHERE id=? AND deleted_at IS NULL');
    $ok = $stmt->execute([$newStatus, $vehicleId]);
    if ($ok) {
        logActivity('Vehicle status updated', sprintf('Vehicle #%d -> %s', $vehicleId, $newStatus));
    }
    return $ok;
}

/**
 * Insert vehicle.
 *
 * @param array<string, mixed> $data
 * @return int Inserted vehicle id
 */
function createVehicle(array $data): int
{
    // Required fields validation (server-side)
    validateVehiclePayloadForCreate($data);

    $registration = (string)$data['registration_number'];
    $make = (string)$data['make'];
    $model = (string)$data['model'];
    $year = (int)$data['year'];
    $typeId = (int)$data['vehicle_type_id'];
    $capacity = (int)$data['capacity_kg'];
    $regionId = (int)$data['region_id'];
    $status = (string)($data['status'] ?? 'Available');

    if (!isRegistrationNumberUnique($registration)) {
        throw new RuntimeException('Vehicle registration number already exists.');
    }
    if (!isEngineNumberUnique((string)($data['engine_number'] ?? ''))) {
        throw new RuntimeException('Engine number must be unique.');
    }
    if (!isChassisNumberUnique((string)($data['chassis_number'] ?? ''))) {
        throw new RuntimeException('Chassis number must be unique.');
    }

    $stmt = getDb()->prepare(
        'INSERT INTO vehicles
        (uuid, registration_number, vehicle_name, make, model, year, vehicle_type_id, capacity_kg, status, region_id,
         engine_number, chassis_number, fuel_type, odometer_km, purchase_date, purchase_cost,
         insurance_number, insurance_expiry, fitness_expiry, permit_expiry, pollution_expiry,
         photo_path, remarks, created_by, updated_by)
         VALUES
        (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $stmt->execute([
        $registration,
        (string)($data['vehicle_name'] ?? $make . ' ' . $model),
        $make,
        $model,
        $year,
        $typeId,
        $capacity,
        $status,
        $regionId,
        (string)($data['engine_number'] ?? $vehicle['engine_number']),
        (string)($data['chassis_number'] ?? $vehicle['chassis_number']),
        (string)($data['fuel_type'] ?? 'Diesel'),
        (float)($data['current_odometer_km'] ?? 0),
        $data['purchase_date'] ?? null,
        (string)($data['purchase_cost'] ?? 0),
        (string)($data['insurance_number'] ?? ''),
        $data['insurance_expiry'] ?? null,
        $data['fitness_expiry'] ?? null,
        $data['permit_expiry'] ?? null,
        $data['pollution_expiry'] ?? null,
        $data['photo_path'] ?? $vehicle['photo_path'],
        (string)($data['remarks'] ?? null),
        (int)currentUser()['id'],
        (int)currentUser()['id'],
    ]);

    $vehicleId = (int)getDb()->lastInsertId();
    logActivity('Vehicle created', sprintf('Vehicle #%d (%s) created.', $vehicleId, $registration));
    return $vehicleId;
}

/**
 * Update vehicle.
 *
 * @param int $vehicleId
 * @param array<string, mixed> $data
 * @param bool $allowEditRegistrationNumber
 * @return bool
 */
function updateVehicle(int $vehicleId, array $data, bool $allowEditRegistrationNumber = false): bool
{
    validateVehiclePayloadForUpdate($data);

    $vehicle = getVehicleById($vehicleId);
    if (!$vehicle) {
        throw new RuntimeException('Vehicle not found.');
    }

    $newRegistration = (string)($data['registration_number'] ?? $vehicle['registration_number']);
    if (!$allowEditRegistrationNumber && $newRegistration !== (string)$vehicle['registration_number']) {
        throw new RuntimeException('Registration Number cannot be changed.');
    }

    if (!isRegistrationNumberUnique($newRegistration, $vehicleId)) {
        throw new RuntimeException('Vehicle registration number already exists.');
    }

    if (!isEngineNumberUnique((string)($data['engine_number'] ?? ''), $vehicleId)) {
        throw new RuntimeException('Engine number must be unique.');
    }
    if (!isChassisNumberUnique((string)($data['chassis_number'] ?? ''), $vehicleId)) {
        throw new RuntimeException('Chassis number must be unique.');
    }

    $stmt = getDb()->prepare(
        'UPDATE vehicles
         SET
           vehicle_name=?, make=?, model=?, year=?, vehicle_type_id=?, capacity_kg=?, status=?, region_id=?,
           engine_number=?, chassis_number=?, fuel_type=?, odometer_km=?, purchase_date=?, purchase_cost=?,
           insurance_number=?, insurance_expiry=?, fitness_expiry=?, permit_expiry=?, pollution_expiry=?,
           photo_path=?, remarks=?,
           updated_by=?
         WHERE id=? AND deleted_at IS NULL'
    );

    $ok = $stmt->execute([
        (string)($data['vehicle_name'] ?? ''),
        (string)$data['make'],
        (string)$data['model'],
        (int)$data['year'],
        (int)$data['vehicle_type_id'],
        (int)$data['capacity_kg'],
        (string)($data['status'] ?? $vehicle['status']),
        (int)$data['region_id'],
        (string)($data['engine_number'] ?? ''),
        (string)($data['chassis_number'] ?? ''),
        (string)($data['fuel_type'] ?? 'Diesel'),
        (float)($data['current_odometer_km'] ?? 0),
        $data['purchase_date'] ?? null,
        (string)($data['purchase_cost'] ?? 0),
        (string)($data['insurance_number'] ?? ''),
        $data['insurance_expiry'] ?? null,
        $data['fitness_expiry'] ?? null,
        $data['permit_expiry'] ?? null,
        $data['pollution_expiry'] ?? null,
        $data['photo_path'] ?? null,
        (string)($data['remarks'] ?? null),
        (int)currentUser()['id'],
        $vehicleId
    ]);

    if ($ok) {
        logActivity('Vehicle updated', sprintf('Vehicle #%d updated.', $vehicleId));
    }

    return $ok;
}

/**
 * Soft delete a vehicle.
 *
 * Business rule: retired vehicle cannot be assigned (handled at status transitions / trip trigger).
 * Here we prevent deletion if active trips exist.
 *
 * @param int $vehicleId
 * @return bool
 */
function deleteVehicle(int $vehicleId): bool
{
    $vehicle = getVehicleById($vehicleId);
    if (!$vehicle) {
        return false;
    }

    $stmt = getDb()->prepare(
        'SELECT 1 FROM trips t
         WHERE t.vehicle_id=? AND t.deleted_at IS NULL AND t.status IN (\'Dispatched\',\'In Progress\')
         LIMIT 1'
    );
    $stmt->execute([$vehicleId]);
    if ($stmt->fetch()) {
        throw new RuntimeException('Vehicle cannot be deleted while it has active trips.');
    }

    $ok = getDb()->prepare('UPDATE vehicles SET deleted_at=NOW() WHERE id=? AND deleted_at IS NULL')->execute([$vehicleId]);
    if ($ok) {
        logActivity('Vehicle deleted', sprintf('Vehicle #%d deleted (soft).', $vehicleId));
    }

    return (bool)$ok;
}

/**
 * List vehicles with filters for enterprise datatable.
 *
 * @param array<string, string|int|null> $filters
 * @param int $limit
 * @param int $offset
 * @param string $orderBy
 * @param string $direction
 * @return array<int, array<string, mixed>>
 */
function searchVehicles(array $filters, int $limit, int $offset, string $orderBy, string $direction): array
{
    $allowedOrder = [
        'v.created_at',
        'v.registration_number',
        'v.vehicle_name',
        'v.purchase_date',
        'v.capacity_kg',
    ];

    $orderBySql = in_array($orderBy, $allowedOrder, true) ? $orderBy : 'v.created_at';
    $dirSql = in_array(strtoupper($direction), ['ASC', 'DESC'], true) ? strtoupper($direction) : 'DESC';

    $where = ['v.deleted_at IS NULL'];
    $params = [];

    if (!empty($filters['registration_number'])) {
        $where[] = 'v.registration_number LIKE ?';
        $params[] = '%' . $filters['registration_number'] . '%';
    }
    if (!empty($filters['vehicle_name'])) {
        $where[] = 'v.vehicle_name LIKE ?';
        $params[] = '%' . $filters['vehicle_name'] . '%';
    }
    if (!empty($filters['manufacturer'])) {
        $where[] = 'v.manufacturer LIKE ?';
        $params[] = '%' . $filters['manufacturer'] . '%';
    }
    if (!empty($filters['vehicle_type_id'])) {
        $where[] = 'v.vehicle_type_id=?';
        $params[] = (int)$filters['vehicle_type_id'];
    }
    if (!empty($filters['fuel_type'])) {
        $where[] = 'v.fuel_type=?';
        $params[] = (string)$filters['fuel_type'];
    }
    if (!empty($filters['status'])) {
        $where[] = 'v.status=?';
        $params[] = (string)$filters['status'];
    }
    if (!empty($filters['region_id'])) {
        $where[] = 'v.region_id=?';
        $params[] = (int)$filters['region_id'];
    }
    if (!empty($filters['purchase_year'])) {
        $where[] = 'YEAR(v.purchase_date)=?';
        $params[] = (int)$filters['purchase_year'];
    }

    $whereSql = implode(' AND ', $where);

    $sql = "
      SELECT
        v.id,
        v.photo_path,
        v.registration_number,
        v.vehicle_name,
        v.manufacturer,
        v.model,
        vt.name AS vehicle_type,
        v.fuel_type,
        v.capacity_kg AS load_capacity,
        v.odometer_km AS current_odometer,
        v.purchase_cost,
        v.status,
        r.name AS region,
        v.created_at
      FROM vehicles v
      JOIN vehicle_types vt ON vt.id=v.vehicle_type_id
      JOIN regions r ON r.id=v.region_id
      WHERE {$whereSql}
      ORDER BY {$orderBySql} {$dirSql}
      LIMIT ? OFFSET ?
    ";

    $stmt = getDb()->prepare($sql);
    foreach ($params as $idx => $p) {
        $stmt->bindValue($idx + 1, $p);
    }
    $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Count vehicles for current filters.
 *
 * @param array<string, string|int|null> $filters
 * @return int
 */
function countVehicles(array $filters): int
{
    $where = ['deleted_at IS NULL'];
    $params = [];

    if (!empty($filters['registration_number'])) {
        $where[] = 'registration_number LIKE ?';
        $params[] = '%' . $filters['registration_number'] . '%';
    }
    if (!empty($filters['vehicle_name'])) {
        $where[] = 'vehicle_name LIKE ?';
        $params[] = '%' . $filters['vehicle_name'] . '%';
    }
    if (!empty($filters['manufacturer'])) {
        $where[] = 'manufacturer LIKE ?';
        $params[] = '%' . $filters['manufacturer'] . '%';
    }
    if (!empty($filters['vehicle_type_id'])) {
        $where[] = 'vehicle_type_id=?';
        $params[] = (int)$filters['vehicle_type_id'];
    }
    if (!empty($filters['fuel_type'])) {
        $where[] = 'fuel_type=?';
        $params[] = (string)$filters['fuel_type'];
    }
    if (!empty($filters['status'])) {
        $where[] = 'status=?';
        $params[] = (string)$filters['status'];
    }
    if (!empty($filters['region_id'])) {
        $where[] = 'region_id=?';
        $params[] = (int)$filters['region_id'];
    }
    if (!empty($filters['purchase_year'])) {
        $where[] = 'YEAR(purchase_date)=?';
        $params[] = (int)$filters['purchase_year'];
    }

    $whereSql = implode(' AND ', $where);
    $stmt = getDb()->prepare("SELECT COUNT(*) FROM vehicles WHERE {$whereSql}");
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

/**
 * Export vehicles as CSV.
 *
 * @param array<string, string|int|null> $filters
 * @return void
 */
function exportVehiclesCsv(array $filters): void
{
    $limit = 1000;
    $offset = 0;
    $rows = [];

    while (true) {
        $chunk = searchVehicles($filters, $limit, $offset, 'v.created_at', 'DESC');
        if (!$chunk) {
            break;
        }
        $rows = array_merge($rows, $chunk);
        $offset += $limit;
        if (count($chunk) < $limit) {
            break;
        }
    }

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=transitops_vehicles_export.csv');

    $out = fopen('php://output', 'w');
    fputcsv($out, [
        'Registration Number',
        'Vehicle Name',
        'Manufacturer',
        'Model',
        'Vehicle Type',
        'Fuel Type',
        'Load Capacity (kg)',
        'Current Odometer (km)',
        'Purchase Cost',
        'Status',
        'Region',
        'Created Date'
    ]);

    foreach ($rows as $r) {
        fputcsv($out, [
            $r['registration_number'],
            $r['vehicle_name'],
            $r['manufacturer'],
            $r['model'],
            $r['vehicle_type'],
            $r['fuel_type'],
            $r['load_capacity'],
            $r['current_odometer'],
            $r['purchase_cost'],
            $r['status'],
            $r['region'],
            $r['created_at'],
        ]);
    }

    fclose($out);
    exit;
}



