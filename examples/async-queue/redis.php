<?php

declare(strict_types=1);

return [
    'default' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'auth' => null,
        'db' => 0,
        'timeout' => 0.0,
        'options' => [
            'min_active' => 1,
            'max_active' => 10,
            'wait_timeout' => 3.0,
            'heartbeat' => 60.0,
            'max_idle_time' => 60.0,
            'max_lifetime' => 0.0,
        ],
    ],
    'cache' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'auth' => null,
        'db' => 1,
        'timeout' => 0.0,
        'options' => [],
    ],
    'queue' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'auth' => null,
        'db' => 2,
        'timeout' => 0.0,
        'options' => [],
    ],
];
