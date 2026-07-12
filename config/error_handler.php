<?php
/**
 * Central error logging. Detailed errors are never rendered in production.
 */
declare(strict_types=1);

set_exception_handler(static function (Throwable $exception): void {
    error_log(sprintf('[TransitOps] %s in %s:%d', $exception->getMessage(), $exception->getFile(), $exception->getLine()));
    http_response_code(500);
    if (PHP_SAPI !== 'cli') {
        echo 'An unexpected error occurred. Please try again later.';
    }
});
