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
        'operations' => 'Operations',
        'driver' => 'Driver',
        'viewer' => 'Viewer',
    ];
}
