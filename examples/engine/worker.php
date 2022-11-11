<?php

use Larmias\Engine\DriverConfigManager;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;

return [
    'driver' => DriverConfigManager::WORKER_S,
    'workers' => [
        [
            'name' => 'http',
            'type' => WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9501,
            'settings' => [
                'worker_num' => 1,
                'task_worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_REQUEST => function ($request,$response) {
                    $response->end('hello');
                }
            ]
        ]
    ],
    'settings'  => [],
    'callbacks' => [],
];