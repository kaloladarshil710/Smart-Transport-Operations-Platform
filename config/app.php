<?php

declare(strict_types=1);

return [
    'name' => getenv('APP_NAME') ?: 'TransitOps',
    'environment' => getenv('APP_ENV') ?: 'production',
    'debug' => filter_var(getenv('APP_DEBUG') ?: false, FILTER_VALIDATE_BOOL),
    'url' => rtrim(getenv('APP_URL') ?: '', '/'),
    'timezone' => getenv('APP_TIMEZONE') ?: 'Asia/Kolkata',
    'session' => [
        'name' => getenv('SESSION_NAME') ?: 'transitops_session',
        'lifetime_minutes' => (int) (getenv('SESSION_LIFETIME') ?: 120),
    ],
];
