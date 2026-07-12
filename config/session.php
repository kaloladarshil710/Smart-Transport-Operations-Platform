<?php
/**
 * Session hardening helpers.
 */
declare(strict_types=1);

function regenerateSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_regenerate_id(true);
    }
}
