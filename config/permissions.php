<?php
/**
 * Permission mapping.
 */
declare(strict_types=1);

function getPermissions(): array
{
    return [
        'dashboard' => ['admin', 'fleet_manager', 'dispatcher', 'safety_officer', 'financial_analyst', 'operations', 'viewer'],
        'vehicles' => ['admin', 'fleet_manager', 'dispatcher', 'operations'],
        'drivers' => ['admin', 'fleet_manager', 'dispatcher', 'safety_officer', 'operations'],
        'trips' => ['admin', 'fleet_manager', 'dispatcher', 'operations'],
        'maintenance' => ['admin', 'fleet_manager', 'safety_officer', 'operations'],
        'fuel' => ['admin', 'financial_analyst', 'operations'],
        'expenses' => ['admin', 'financial_analyst', 'operations'],
        'reports' => ['admin', 'fleet_manager', 'financial_analyst', 'operations', 'viewer'],
        'analytics' => ['admin', 'fleet_manager', 'financial_analyst', 'operations'],
        'notifications' => ['admin', 'fleet_manager', 'dispatcher', 'safety_officer', 'financial_analyst', 'operations'],
        'users' => ['admin'],
        'settings' => ['admin'],
        'profile' => ['admin', 'fleet_manager', 'dispatcher', 'safety_officer', 'financial_analyst', 'operations', 'driver', 'viewer'],
        'activity' => ['admin'],
    ];
}

function canAccess(string $module): bool
{
    $role = currentUser()['role'] ?? '';
    return in_array($role, getPermissions()[$module] ?? [], true);
}
