<?php

use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\ShareMemory\Server as ShareMemoryServer;

return [
    'driver' => \Larmias\Engine\WorkerMan\WorkerMan::class,
    'workers' => [
        [
            'name' => 'tcp',
            'type' => WorkerType::TCP_SERVER,
            'host' => '0.0.0.0',
            'port' => 2000,
            'settings' => [
                'worker_num' => 1,
                'protocol' => \Workerman\Protocols\Frame::class,
            ],
            'callbacks' => [
                Event::ON_RECEIVE => [ShareMemoryServer::class,ShareMemoryServer::ON_RECEIVE]
            ]
        ],
        [
            'name' => 'watcherProcess',
            'type' => WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
                 'watch' => [
                    'enabled'  => true,
                    'includes' => [

                    ],
                ],
            ],
            'callbacks' => [
                Event::ON_WORKER_START => [\Larmias\Engine\Process\Handler\WorkerHotUpdateHandler::class,'handle'],
            ]
        ]
    ],
    'settings'  => [

    ],
    'callbacks' => [
        Event::ON_WORKER_START => function () {

        }
    ],
];