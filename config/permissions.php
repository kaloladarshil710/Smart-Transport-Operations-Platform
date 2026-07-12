<?php
/**
 * Permission mapping.
 */
declare(strict_types=1);

function getPermissions(): array
{
    return [
        'dashboard' => ['admin', 'fleet_manager', 'operations', 'viewer'],
        'vehicles' => ['admin', 'fleet_manager', 'operations'],
        'drivers' => ['admin', 'fleet_manager', 'operations'],
        'trips' => ['admin', 'fleet_manager', 'operations'],
        'maintenance' => ['admin', 'fleet_manager', 'operations'],
        'fuel' => ['admin', 'fleet_manager', 'operations'],
        'expenses' => ['admin', 'fleet_manager', 'operations'],
        'reports' => ['admin', 'fleet_manager', 'operations', 'viewer'],
        'analytics' => ['admin', 'fleet_manager', 'operations'],
        'notifications' => ['admin', 'fleet_manager', 'operations'],
        'users' => ['admin'],
        'settings' => ['admin'],
    ];
}

function canAccess(string $module): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, getPermissions()[$module] ?? [], true);
}
