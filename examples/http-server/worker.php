<?php

use Larmias\Engine\DriverConfigManager;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\HttpServer\Server as HttpServer;

return [
    'driver' => DriverConfigManager::WORKER_S,
    'workers' => [
        [
            'name' => 'http',
            'type' => WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9863,
            'settings' => [
                'worker_num' => 1,
                'task_worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class,HttpServer::ON_REQUEST]
            ]
        ]
    ],
    'settings'  => [],
    'callbacks' => [],
];