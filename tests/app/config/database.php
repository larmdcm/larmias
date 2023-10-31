<?php

declare(strict_types=1);

use function Larmias\Framework\env;

return [
    'default' => [
        'type' => env('DB_TYPE', 'mysql'),
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => (int)env('DB_PORT', 3306),
        'username' => env('DB_USERNAME', 'test'),
        'password' => env('DB_PASSWORD', ''),
        'database' => env('DB_DATABASE', 'larmias'),
        'charset' => 'utf8mb4',
        'dsn' => '',
        'socket' => '',
        'options' => [],
        'prefix' => env('DB_PREFIX', ''),
        'pool' => [
            'min_active' => 1,
            'max_active' => 10,
            'wait_timeout' => 3.0,
            'heartbeat' => 0.0,
            'max_idle_time' => 60.0,
            'max_lifetime' => 0.0,
        ]
    ]
];