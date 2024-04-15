<?php

declare(strict_types=1);

use function Larmias\Support\env;

return [
    'default' => env('DB_DEFAULT', 'mysql'),
    'connections' => [
        'mysql' => [
            'type' => env('DB_CONNECTION', 'mysql'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => (int)env('DB_PORT', 3306),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', 'root'),
            'database' => env('DB_DATABASE', 'test'),
            'charset' => env('DB_CHARSET', 'utf8mb4'),
            'dsn' => '',
            'socket' => '',
            'options' => [],
            'prefix' => '',
            'pool' => [
                'min_active' => 1,
                'max_active' => 20,
                'wait_timeout' => 3.0,
                'heartbeat' => 60.0,
                'max_idle_time' => 60.0,
                'max_lifetime' => 0.0,
            ]
        ]
    ]
];