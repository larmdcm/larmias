<?php
declare(strict_types=1);

return [
    'default' => [
        'type' => 'mysql',
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'larmias_test',
        'password' => 'larmias_test',
        'database' => 'larmias_test',
        'charset' => 'utf8mb4',
        'dsn' => '',
        'socket' => '',
        'options' => [],
        'prefix' => 't_',
        'pool' => [
            'min_active' => 10,
            'max_active' => 20,
            'wait_timeout' => 3.0,
            'heartbeat' => 30.0,
            'max_idle_time' => 60.0,
            'max_lifetime' => 0.0,
        ]
    ]
];