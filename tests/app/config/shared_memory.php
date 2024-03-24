<?php

declare(strict_types=1);

return [
    'client' => [
        'password' => '123456',
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