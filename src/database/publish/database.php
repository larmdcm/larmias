<?php
declare(strict_types=1);

return [
    'default' => [
        'type' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => '',
        'password' => '',
        'database' => '',
        'charset' => 'utf8mb4',
        'dsn' => '',
        'socket' => '',
        'options' => [],
        'prefix' => '',
        'pool' => [
            'min_active' => 1,
            'max_active' => 10,
            'wait_timeout' => 3.0,
            'heartbeat' => 60.0,
            'max_idle_time' => 60.0,
            'max_lifetime' => 0.0,
        ]
    ]
];