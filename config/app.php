<?php

declare(strict_types=1);

return [
    'name' => 'GYMFIT',
    'version' => '2.0.0',
    'debug' => false,
    'rate_limit' => [
        'max_requests' => 60,
        'window_minutes' => 1,
    ],
    'session' => [
        'lifetime' => 7200,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ],
    'password' => [
        'min_length' => 8,
        'require_special' => true,
        'require_number' => true,
        'require_uppercase' => true,
    ],
    'cors' => [
        'allowed_origins' => ['http://localhost:8000'],
        'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
        'allowed_headers' => ['Content-Type', 'X-CSRF-Token', 'Authorization'],
    ],
];
