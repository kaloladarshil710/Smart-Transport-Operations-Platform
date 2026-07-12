<?php
/**
 * Validation helpers for TransitOps.
 *
 * Central place for server-side validation used across modules.
 *
 * @package TransitOps
 */

declare(strict_types=1);

/**
 * Validate payload for creating a vehicle.
 *
 * @param array<string, mixed> $data
 * @return void
 */
function validateVehiclePayloadForCreate(array $data): void
{
    validateVehicleRequiredFields($data);

    $capacity = (int)($data['capacity_kg'] ?? 0);
    if ($capacity <= 0) {
        throw new InvalidArgumentException('Load capacity must be greater than zero.');
    }

    $purchaseCost = (float)($data['purchase_cost'] ?? 0);
    if ($purchaseCost < 0) {
        throw new InvalidArgumentException('Purchase cost cannot be negative.');
    }

    validateVehicleDates($data);
}

/**
 * Validate payload for updating a vehicle.
 *
 * @param array<string, mixed> $data
 * @return void
 */
function validateVehiclePayloadForUpdate(array $data): void
{
    validateVehicleRequiredFields($data);

    $capacity = (int)($data['capacity_kg'] ?? 0);
    if ($capacity <= 0) {
        throw new InvalidArgumentException('Load capacity must be greater than zero.');
    }

    $purchaseCost = (float)($data['purchase_cost'] ?? 0);
    if ($purchaseCost < 0) {
        throw new InvalidArgumentException('Purchase cost cannot be negative.');
    }

    validateVehicleDates($data);
}

/**
 * Validate required fields common to create/update.
 *
 * @param array<string, mixed> $data
 * @return void
 */
function validateVehicleRequiredFields(array $data): void
{
    $required = [
        'registration_number',
        'vehicle_name',
        'make',
        'model',
        'year',
        'vehicle_type_id',
        'capacity_kg',
        'region_id',
        'fuel_type',
        'current_odometer_km',
        'purchase_cost',
        'status',
    ];

    foreach ($required as $key) {
        if (!array_key_exists($key, $data)) {
            throw new InvalidArgumentException('Missing field: ' . $key);
        }
    }

    if (trim((string)$data['registration_number']) === '') {
        throw new InvalidArgumentException('Registration number is required.');
    }

    if (trim((string)$data['vehicle_name']) === '') {
        throw new InvalidArgumentException('Vehicle name is required.');
    }

    if (trim((string)$data['make']) === '') {
        throw new InvalidArgumentException('Manufacturer is required.');
    }

    if (trim((string)$data['model']) === '') {
        throw new InvalidArgumentException('Model is required.');
    }

    $year = (int)$data['year'];
    if ($year < 1980 || $year > (int)date('Y') + 1) {
        throw new InvalidArgumentException('Invalid purchase year.');
    }

    $regionId = (int)$data['region_id'];
    if ($regionId <= 0) {
        throw new InvalidArgumentException('Region is required.');
    }

    $typeId = (int)$data['vehicle_type_id'];
    if ($typeId <= 0) {
        throw new InvalidArgumentException('Vehicle type is required.');
    }
}

/**
 * Validate date fields (nullable).
 *
 * @param array<string, mixed> $data
 * @return void
 */
function validateVehicleDates(array $data): void
{
    $dateKeys = [
        'purchase_date',
        'insurance_expiry',
        'fitness_expiry',
        'permit_expiry',
        'pollution_expiry',
    ];

    foreach ($dateKeys as $key) {
        if (!array_key_exists($key, $data)) {
            continue;
        }

        $val = $data[$key];
        if ($val === null || $val === '') {
            continue;
        }

        $d = DateTime::createFromFormat('Y-m-d', (string)$val);
        if (!$d || $d->format('Y-m-d') !== (string)$val) {
            throw new InvalidArgumentException('Invalid date for ' . $key . '. Expected Y-m-d.');
        }
    }

}

/**
 * Validate status change.
 *
 * @param string $status
 * @return void
 */
function validateVehicleStatus(string $status): void
{
    $allowed = ['Available', 'On Trip', 'In Shop', 'Retired', 'Sold'];
    if (!in_array($status, $allowed, true)) {
        throw new InvalidArgumentException('Invalid vehicle status.');
    }
}

/**
 * Sanitize string input.
 *
 * @param mixed $value
 * @return string
 */
function sanitize_string(mixed $value): string
{
    return htmlspecialchars(trim((string)$value), ENT_QUOTES, 'UTF-8');
}

