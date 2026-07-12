<?php
/**
 * Role definitions.
 */
declare(strict_types=1);

function getRoles(): array
{
    return [
        'admin' => 'Administrator',
        'fleet_manager' => 'Fleet Manager',
        'dispatcher' => 'Dispatcher',
        'safety_officer' => 'Safety Officer',
        'financial_analyst' => 'Financial Analyst',
        'operations' => 'Operations',
        'driver' => 'Driver',
        'viewer' => 'Viewer',
    ];
}
