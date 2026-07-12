<?php
/**
 * Simple role middleware.
 */
declare(strict_types=1);

function enforceModuleAccess(string $module): void
{
    requireAuth();

    if (!canAccess($module)) {
        setFlash('danger', 'You do not have permission to access this module.');
        redirect('dashboard.php');
    }
}
