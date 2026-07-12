<?php
/**
 * Trip management core business logic for TransitOps.
 *
 * @package TransitOps
 * @subpackage Trips
 */
declare(strict_types=1);

/**
 * Get trip by ID with full details.
 *
 * @param int $tripId
 * @return array|null
 */
function getTripById(int $tripId): ?array
{
    $stmt = getDb()->prepare("
        SELECT t.*,
               v.registration_number,
               COALESCE(v.vehicle_name, CONCAT(v.make, ' ', v.model)) AS vehicle_name,
               v.status AS vehicle_status,
               v.capacity_kg,
               v.fuel_efficiency,
               d.full_name AS driver_name,
               d.phone AS driver_phone,
               d.license_number,
               d.license_expiry,
               d.status AS driver_status,
               d.safety_score
        FROM trips t
        JOIN vehicles v ON v.id = t.vehicle_id
        JOIN drivers d ON d.id = t.driver_id
        WHERE t.id = ? AND t.deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([$tripId]);
    $trip = $stmt->fetch();
    return $trip ?: null;
}

/**
 * Get trip by trip_number.
 *
 * @param string $tripNumber
 * @return array|null
 */
function getTripByNumber(string $tripNumber): ?array
{
    $stmt = getDb()->prepare("
        SELECT t.*, v.registration_number, d.full_name AS driver_name
        FROM trips t
        JOIN vehicles v ON v.id = t.vehicle_id
        JOIN drivers d ON d.id = t.driver_id
        WHERE t.trip_number = ? AND t.deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([$tripNumber]);
    $trip = $stmt->fetch();
    return $trip ?: null;
}

/**
 * Auto-generate next trip number.
 *
 * Format: TRP-YYYY-NNNN
 *
 * @return string
 */
function generateTripNumber(): string
{
    $year = date('Y');
    $stmt = getDb()->prepare("
        SELECT COUNT(*) + 1 AS next_num
        FROM trips
        WHERE YEAR(created_at) = ?
    ");
    $stmt->execute([$year]);
    $row = $stmt->fetch();
    $num = str_pad((string)($row['next_num'] ?? 1), 4, '0', STR_PAD_LEFT);
    return 'TRP-' . $year . '-' . $num;
}

/**
 * Create a new trip.
 *
 * @param array $data Trip data
 * @return int Created trip ID
 * @throws RuntimeException
 */
function createTrip(array $data): int
{
    $tripNumber = $data['trip_number'] ?? generateTripNumber();
    $vehicleId = (int) ($data['vehicle_id'] ?? 0);
    $driverId = (int) ($data['driver_id'] ?? 0);
    $cargoWeight = (int) ($data['cargo_weight_kg'] ?? 0);
    $origin = trim((string) ($data['origin'] ?? ''));
    $destination = trim((string) ($data['destination'] ?? ''));
    $startDate = trim((string) ($data['start_date'] ?? date('Y-m-d')));
    $plannedDistance = (float) ($data['planned_distance_km'] ?? 0);
    $revenue = (float) ($data['revenue'] ?? 0);
    $cargoType = trim((string) ($data['cargo_type'] ?? ''));
    $remarks = trim((string) ($data['remarks'] ?? ''));
    $endDate = !empty($data['end_date']) ? trim((string) $data['end_date']) : null;

    if ($vehicleId <= 0 || $driverId <= 0) {
        throw new RuntimeException('Vehicle and driver are required.');
    }

    // Validate vehicle
    $vehicle = getVehicleById($vehicleId);
    if (!$vehicle) {
        throw new RuntimeException('Vehicle not found.');
    }

    // Validate driver
    $driverStmt = getDb()->prepare('SELECT * FROM drivers WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $driverStmt->execute([$driverId]);
    $driver = $driverStmt->fetch();
    if (!$driver) {
        throw new RuntimeException('Driver not found.');
    }

    // Cargo weight check
    if ($cargoWeight > 0 && $cargoWeight > (int) $vehicle['capacity_kg']) {
        throw new RuntimeException('Cargo weight exceeds vehicle capacity of ' . $vehicle['capacity_kg'] . ' kg.');
    }

    $stmt = getDb()->prepare("
        INSERT INTO trips
        (uuid, trip_number, vehicle_id, driver_id, cargo_weight_kg, origin, destination,
         start_date, end_date, status, revenue, planned_distance_km, cargo_type, remarks,
         created_by, updated_by)
        VALUES (UUID(), ?, ?, ?, ?, ?, ?, ?, ?, 'Draft', ?, ?, ?, ?, ?, ?)
    ");

    $stmt->execute([
        $tripNumber,
        $vehicleId,
        $driverId,
        $cargoWeight,
        $origin,
        $destination,
        $startDate,
        $endDate,
        $revenue,
        $plannedDistance,
        $cargoType ?: null,
        $remarks ?: null,
        (int) currentUser()['id'],
        (int) currentUser()['id'],
    ]);

    $tripId = (int) getDb()->lastInsertId();

    // Create trip history entry
    addTripHistory($tripId, 'Draft', 'Trip created');

    // Log activity
    logActivity('Trip created', "Trip #{$tripId} ({$tripNumber}) created from {$origin} to {$destination}.");

    return $tripId;
}

/**
 * Update trip (only allowed in Draft status).
 *
 * @param int $tripId
 * @param array $data
 * @return bool
 * @throws RuntimeException
 */
function updateTrip(int $tripId, array $data): bool
{
    $trip = getTripById($tripId);
    if (!$trip) {
        throw new RuntimeException('Trip not found.');
    }
    if ($trip['status'] !== 'Draft') {
        throw new RuntimeException('Only draft trips can be edited.');
    }

    $fields = [];
    $params = [];

    $editable = [
        'vehicle_id', 'driver_id', 'cargo_weight_kg', 'origin', 'destination',
        'start_date', 'end_date', 'planned_distance_km', 'revenue', 'cargo_type', 'remarks',
    ];

    foreach ($editable as $field) {
        if (array_key_exists($field, $data)) {
            $fields[] = "$field = ?";
            $params[] = $data[$field];
        }
    }

    if (empty($fields)) {
        return false;
    }

    // Validate vehicle/driver if changed
    if (isset($data['vehicle_id'])) {
        $vehicle = getVehicleById((int) $data['vehicle_id']);
        if (!$vehicle) {
            throw new RuntimeException('Vehicle not found.');
        }
    }

    if (isset($data['cargo_weight_kg']) && isset($data['vehicle_id'])) {
        $vehicle = $vehicle ?? getVehicleById((int) $data['vehicle_id']);
        if ((int) $data['cargo_weight_kg'] > (int) $vehicle['capacity_kg']) {
            throw new RuntimeException('Cargo weight exceeds vehicle capacity.');
        }
    }

    $params[] = $tripId;
    $sql = 'UPDATE trips SET ' . implode(', ', $fields) . ', updated_at = NOW(), updated_by = ? WHERE id = ?';
    $params[] = (int) currentUser()['id'];
    $params[] = $tripId;

    $stmt = getDb()->prepare($sql);
    $ok = $stmt->execute($params);

    if ($ok) {
        addTripHistory($tripId, $trip['status'], 'Trip updated');
        logActivity('Trip updated', "Trip #{$tripId} updated.");
    }

    return $ok;
}

/**
 * Dispatch a trip - validates all business rules and transitions status.
 *
 * @param int $tripId
 * @return array{success: bool, message?: string}
 */
function dispatchTrip(int $tripId): array
{
    $trip = getTripById($tripId);
    if (!$trip) {
        return ['success' => false, 'message' => 'Trip not found.'];
    }
    if ($trip['status'] !== 'Draft') {
        return ['success' => false, 'message' => 'Only draft trips can be dispatched.'];
    }

    $vehicleId = (int) $trip['vehicle_id'];
    $driverId = (int) $trip['driver_id'];

    // Validate vehicle availability
    $vehicle = getVehicleById($vehicleId);
    if (!$vehicle) {
        return ['success' => false, 'message' => 'Vehicle not found.'];
    }
    if ($vehicle['status'] !== 'Available') {
        $statusMap = [
            'On Trip' => 'Vehicle is currently on another trip.',
            'In Shop' => 'Vehicle is in maintenance. Cannot dispatch.',
            'Retired' => 'Vehicle has been retired.',
        ];
        $msg = $statusMap[$vehicle['status']] ?? 'Vehicle is not available.';
        return ['success' => false, 'message' => $msg];
    }

    // Validate driver availability
    $driverStmt = getDb()->prepare('SELECT * FROM drivers WHERE id = ? AND deleted_at IS NULL LIMIT 1');
    $driverStmt->execute([$driverId]);
    $driver = $driverStmt->fetch();
    if (!$driver) {
        return ['success' => false, 'message' => 'Driver not found.'];
    }
    if ($driver['status'] !== 'Available') {
        $statusMap = [
            'On Trip' => 'Driver is currently on another trip.',
            'Suspended' => 'Driver is suspended.',
            'Off Duty' => 'Driver is off duty.',
        ];
        $msg = $statusMap[$driver['status']] ?? 'Driver is not available.';
        return ['success' => false, 'message' => $msg];
    }

    // Validate license expiry
    if (!empty($driver['license_expiry']) && strtotime($driver['license_expiry']) < strtotime('today')) {
        return ['success' => false, 'message' => 'Driver license has expired (' . date('d M Y', strtotime($driver['license_expiry'])) . ').'];
    }

    // Validate cargo weight
    if ((int) $trip['cargo_weight_kg'] > (int) $vehicle['capacity_kg']) {
        return ['success' => false, 'message' => 'Cargo weight (' . $trip['cargo_weight_kg'] . ' kg) exceeds vehicle capacity (' . $vehicle['capacity_kg'] . ' kg).'];
    }

    // Check for duplicate assignment
    $dupCheck = getDb()->prepare("
        SELECT 1 FROM trips
        WHERE status IN ('Dispatched', 'In Progress')
        AND (vehicle_id = ? OR driver_id = ?)
        AND id != ? AND deleted_at IS NULL
        LIMIT 1
    ");
    $dupCheck->execute([$vehicleId, $driverId, $tripId]);
    if ($dupCheck->fetch()) {
        return ['success' => false, 'message' => 'Vehicle or driver is already assigned to an active trip.'];
    }

    // All validations passed - dispatch
    $now = date('Y-m-d H:i:s');
    getDb()->prepare("UPDATE trips SET status = 'Dispatched', dispatch_at = ? WHERE id = ?")
        ->execute([$now, $tripId]);

    // Update vehicle and driver status
    getDb()->prepare("UPDATE vehicles SET status = 'On Trip' WHERE id = ?")->execute([$vehicleId]);
    getDb()->prepare("UPDATE drivers SET status = 'On Trip' WHERE id = ?")->execute([$driverId]);

    // Trip history
    addTripHistory($tripId, 'Dispatched', 'Trip dispatched successfully.');

    // Log activity
    logActivity('Trip dispatched', "Trip #{$tripId} ({$trip['trip_number']}) dispatched. Vehicle: {$vehicle['registration_number']}, Driver: {$driver['full_name']}.");

    return ['success' => true, 'message' => 'Trip dispatched successfully.'];
}

/**
 * Mark trip as completed.
 *
 * @param int $tripId
 * @param array $data {arrival_date, end_odometer, fuel_used, actual_distance, revenue, trip_notes}
 * @return array{success: bool, message?: string}
 */
function completeTrip(int $tripId, array $data = []): array
{
    $trip = getTripById($tripId);
    if (!$trip) {
        return ['success' => false, 'message' => 'Trip not found.'];
    }
    if (!in_array($trip['status'], ['Dispatched', 'In Progress'], true)) {
        return ['success' => false, 'message' => 'Only dispatched/in-progress trips can be completed.'];
    }

    $arrivalDate = $data['arrival_date'] ?? date('Y-m-d H:i:s');
    $endOdometer = isset($data['end_odometer']) ? (float) $data['end_odometer'] : 0;
    $fuelUsed = isset($data['fuel_used']) ? (float) $data['fuel_used'] : 0;
    $actualDistance = isset($data['actual_distance_km']) ? (float) $data['actual_distance_km'] : ($trip['planned_distance_km'] ?? 0);
    $revenue = isset($data['revenue']) ? (float) $data['revenue'] : (float) $trip['revenue'];
    $tripNotes = trim((string) ($data['trip_notes'] ?? ''));

    $mileage = 0;
    if ($fuelUsed > 0 && $actualDistance > 0) {
        $mileage = $actualDistance / $fuelUsed;
    }

    getDb()->prepare("
        UPDATE trips SET
            status = 'Completed',
            arrival_at = ?,
            end_odometer = ?,
            actual_distance_km = ?,
            fuel_used_liters = ?,
            mileage_kmpl = ?,
            revenue = ?,
            trip_notes = ?,
            updated_at = NOW(),
            updated_by = ?
        WHERE id = ?
    ")->execute([
        $arrivalDate,
        $endOdometer ?: null,
        $actualDistance,
        $fuelUsed ?: null,
        $mileage ?: null,
        $revenue,
        $tripNotes ?: null,
        (int) currentUser()['id'],
        $tripId,
    ]);

    // Restore vehicle and driver status
    getDb()->prepare("UPDATE vehicles SET status = 'Available' WHERE id = ?")->execute([$trip['vehicle_id']]);
    getDb()->prepare("UPDATE drivers SET status = 'Available' WHERE id = ?")->execute([$trip['driver_id']]);

    // Trip history
    addTripHistory($tripId, 'Completed', 'Trip completed.' . ($tripNotes ? " Notes: {$tripNotes}" : ''));

    logActivity('Trip completed', "Trip #{$tripId} ({$trip['trip_number']}) completed. Revenue: {$revenue}.");

    return ['success' => true, 'message' => 'Trip completed successfully.'];
}

/**
 * Cancel a trip.
 *
 * @param int $tripId
 * @param string $reason
 * @return array{success: bool, message?: string}
 */
function cancelTrip(int $tripId, string $reason = ''): array
{
    $trip = getTripById($tripId);
    if (!$trip) {
        return ['success' => false, 'message' => 'Trip not found.'];
    }
    if (in_array($trip['status'], ['Completed', 'Cancelled'], true)) {
        return ['success' => false, 'message' => 'Trip is already ' . strtolower($trip['status']) . '.'];
    }

    $wasActive = in_array($trip['status'], ['Dispatched', 'In Progress'], true);

    getDb()->prepare("
        UPDATE trips SET
            status = 'Cancelled',
            trip_notes = CONCAT(COALESCE(trip_notes, ''), IF(trip_notes IS NOT NULL, ' | ', ''), 'Cancelled: ', ?),
            updated_at = NOW(),
            updated_by = ?
        WHERE id = ?
    ")->execute([$reason ?: 'No reason provided', (int) currentUser()['id'], $tripId]);

    // Restore vehicle and driver if they were active
    if ($wasActive) {
        getDb()->prepare("UPDATE vehicles SET status = 'Available' WHERE id = ? AND status = 'On Trip'")->execute([$trip['vehicle_id']]);
        getDb()->prepare("UPDATE drivers SET status = 'Available' WHERE id = ? AND status = 'On Trip'")->execute([$trip['driver_id']]);
    }

    addTripHistory($tripId, 'Cancelled', 'Trip cancelled. Reason: ' . ($reason ?: 'No reason'));

    logActivity('Trip cancelled', "Trip #{$tripId} ({$trip['trip_number']}) cancelled. Reason: {$reason}.");

    return ['success' => true, 'message' => 'Trip cancelled successfully.'];
}

/**
 * Add a trip history entry.
 *
 * @param int $tripId
 * @param string $status
 * @param string $note
 * @return int
 */
function addTripHistory(int $tripId, string $status, string $note = ''): int
{
    $stmt = getDb()->prepare('INSERT INTO trip_history (trip_id, status, note, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->execute([$tripId, $status, $note]);
    return (int) getDb()->lastInsertId();
}

/**
 * Get trip history/timeline.
 *
 * @param int $tripId
 * @return array
 */
function getTripHistory(int $tripId): array
{
    $stmt = getDb()->prepare("
        SELECT id, status, note, created_at
        FROM trip_history
        WHERE trip_id = ?
        ORDER BY created_at ASC
    ");
    $stmt->execute([$tripId]);
    return $stmt->fetchAll();
}

/**
 * Search trips with filters, pagination, and sorting.
 *
 * @param array $filters
 * @param int $limit
 * @param int $offset
 * @param string $orderBy
 * @param string $direction
 * @return array
 */
function searchTrips(array $filters, int $limit, int $offset, string $orderBy = 't.created_at', string $direction = 'DESC'): array
{
    $allowedOrder = ['t.created_at', 't.trip_number', 't.start_date', 't.revenue', 't.planned_distance_km', 't.dispatch_at'];
    $orderBySql = in_array($orderBy, $allowedOrder, true) ? $orderBy : 't.created_at';
    $dirSql = in_array(strtoupper($direction), ['ASC', 'DESC'], true) ? strtoupper($direction) : 'DESC';

    $where = ['t.deleted_at IS NULL'];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = 't.status = ?';
        $params[] = (string) $filters['status'];
    }
    if (!empty($filters['vehicle_id'])) {
        $where[] = 't.vehicle_id = ?';
        $params[] = (int) $filters['vehicle_id'];
    }
    if (!empty($filters['driver_id'])) {
        $where[] = 't.driver_id = ?';
        $params[] = (int) $filters['driver_id'];
    }
    if (!empty($filters['trip_number'])) {
        $where[] = 't.trip_number LIKE ?';
        $params[] = '%' . $filters['trip_number'] . '%';
    }
    if (!empty($filters['search'])) {
        $where[] = '(t.trip_number LIKE ? OR v.registration_number LIKE ? OR d.full_name LIKE ? OR t.origin LIKE ? OR t.destination LIKE ?)';
        $s = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$s, $s, $s, $s, $s]);
    }
    if (!empty($filters['date_from'])) {
        $where[] = 't.start_date >= ?';
        $params[] = (string) $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $where[] = 't.start_date <= ?';
        $params[] = (string) $filters['date_to'];
    }
    if (!empty($filters['region_id'])) {
        $where[] = 'v.region_id = ?';
        $params[] = (int) $filters['region_id'];
    }
    if (!empty($filters['vehicle_status'])) {
        $where[] = 'v.status = ?';
        $params[] = (string) $filters['vehicle_status'];
    }

    $whereSql = implode(' AND ', $where);

    $sql = "
        SELECT t.*,
               v.registration_number,
               COALESCE(v.vehicle_name, CONCAT(v.make, ' ', v.model)) AS vehicle_name,
               d.full_name AS driver_name
        FROM trips t
        JOIN vehicles v ON v.id = t.vehicle_id
        JOIN drivers d ON d.id = t.driver_id
        WHERE {$whereSql}
        ORDER BY {$orderBySql} {$dirSql}
        LIMIT ? OFFSET ?
    ";

    $stmt = getDb()->prepare($sql);
    foreach ($params as $i => $p) {
        $stmt->bindValue($i + 1, $p);
    }
    $stmt->bindValue(count($params) + 1, $limit, PDO::PARAM_INT);
    $stmt->bindValue(count($params) + 2, $offset, PDO::PARAM_INT);
    $stmt->execute();

    return $stmt->fetchAll();
}

/**
 * Count trips matching filters.
 *
 * @param array $filters
 * @return int
 */
function countTrips(array $filters): int
{
    $where = ['t.deleted_at IS NULL'];
    $params = [];

    if (!empty($filters['status'])) {
        $where[] = 't.status = ?';
        $params[] = (string) $filters['status'];
    }
    if (!empty($filters['vehicle_id'])) {
        $where[] = 't.vehicle_id = ?';
        $params[] = (int) $filters['vehicle_id'];
    }
    if (!empty($filters['driver_id'])) {
        $where[] = 't.driver_id = ?';
        $params[] = (int) $filters['driver_id'];
    }
    if (!empty($filters['trip_number'])) {
        $where[] = 't.trip_number LIKE ?';
        $params[] = '%' . $filters['trip_number'] . '%';
    }
    if (!empty($filters['search'])) {
        $where[] = '(t.trip_number LIKE ? OR v.registration_number LIKE ? OR d.full_name LIKE ? OR t.origin LIKE ? OR t.destination LIKE ?)';
        $s = '%' . $filters['search'] . '%';
        $params = array_merge($params, [$s, $s, $s, $s, $s]);
    }
    if (!empty($filters['date_from'])) {
        $where[] = 't.start_date >= ?';
        $params[] = (string) $filters['date_from'];
    }
    if (!empty($filters['date_to'])) {
        $where[] = 't.start_date <= ?';
        $params[] = (string) $filters['date_to'];
    }

    $whereSql = implode(' AND ', $where);
    $sql = "SELECT COUNT(*) FROM trips t JOIN vehicles v ON v.id = t.vehicle_id JOIN drivers d ON d.id = t.driver_id WHERE {$whereSql}";

    $stmt = getDb()->prepare($sql);
    $stmt->execute($params);
    return (int) $stmt->fetchColumn();
}

/**
 * Get trip statistics summary.
 *
 * @return array
 */
function getTripStats(): array
{
    try {
        $stmt = getDb()->query("
            SELECT
                COUNT(*) AS total_trips,
                SUM(CASE WHEN status = 'Draft' THEN 1 ELSE 0 END) AS draft,
                SUM(CASE WHEN status = 'Dispatched' THEN 1 ELSE 0 END) AS dispatched,
                SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed,
                SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) AS cancelled,
                COALESCE(SUM(CASE WHEN status = 'Completed' THEN revenue ELSE 0 END), 0) AS total_revenue,
                COALESCE(AVG(CASE WHEN status = 'Completed' THEN actual_distance_km ELSE NULL END), 0) AS avg_distance,
                COALESCE(AVG(CASE WHEN status = 'Completed' THEN mileage_kmpl ELSE NULL END), 0) AS avg_mileage
            FROM trips
            WHERE deleted_at IS NULL
        ");
        return $stmt->fetch() ?: [];
    } catch (Throwable $e) {
        return [];
    }
}

/**
 * Get trips for a specific vehicle.
 *
 * @param int $vehicleId
 * @param int $limit
 * @return array
 */
function getTripsByVehicle(int $vehicleId, int $limit = 10): array
{
    $stmt = getDb()->prepare("
        SELECT t.*, d.full_name AS driver_name
        FROM trips t
        JOIN drivers d ON d.id = t.driver_id
        WHERE t.vehicle_id = ? AND t.deleted_at IS NULL
        ORDER BY t.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$vehicleId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get trips for a specific driver.
 *
 * @param int $driverId
 * @param int $limit
 * @return array
 */
function getTripsByDriver(int $driverId, int $limit = 10): array
{
    $stmt = getDb()->prepare("
        SELECT t.*, v.registration_number
        FROM trips t
        JOIN vehicles v ON v.id = t.vehicle_id
        WHERE t.driver_id = ? AND t.deleted_at IS NULL
        ORDER BY t.created_at DESC
        LIMIT ?
    ");
    $stmt->execute([$driverId, $limit]);
    return $stmt->fetchAll();
}

/**
 * Get current active trip for a vehicle.
 *
 * @param int $vehicleId
 * @return array|null
 */
function getActiveTripForVehicle(int $vehicleId): ?array
{
    $stmt = getDb()->prepare("
        SELECT t.*, d.full_name AS driver_name
        FROM trips t
        JOIN drivers d ON d.id = t.driver_id
        WHERE t.vehicle_id = ? AND t.status IN ('Dispatched', 'In Progress') AND t.deleted_at IS NULL
        LIMIT 1
    ");
    $stmt->execute([$vehicleId]);
    return $stmt->fetch() ?: null;
}

/**
 * Count trips today.
 *
 * @return int
 */
function countTripsToday(): int
{
    $stmt = getDb()->query("SELECT COUNT(*) FROM trips WHERE DATE(start_date) = CURDATE() AND deleted_at IS NULL");
    return (int) $stmt->fetchColumn();
}

/**
 * Trip status to CSS class mapping.
 *
 * @param string $status
 * @return string
 */
function tripStatusClass(string $status): string
{
    return match ($status) {
        'Draft' => 'badge-secondary',
        'Dispatched' => 'badge-info',
        'In Progress' => 'badge-warning',
        'Completed' => 'badge-success',
        'Cancelled' => 'badge-danger',
        default => 'badge-secondary',
    };
}
