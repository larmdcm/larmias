<?php

use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\SharedMemory\Server as SharedMemoryServer;

return [
    'driver' => \Larmias\Engine\Swoole\Driver::class,
    'workers' => [
        [
            'name' => 'tcp',
            'type' => WorkerType::TCP_SERVER,
            'host' => '0.0.0.0',
            'port' => 2000,
            'settings' => [
                'worker_num' => 1,
                'auth_password' => '123456',
                'console_output' => true,
                // 'protocol' => \Workerman\Protocols\Frame::class,
                'protocol' => \Larmias\Codec\Protocol\FrameProtocol::class,
            ],
            'callbacks' => [
                Event::ON_WORKER_START => [SharedMemoryServer::class, 'onWorkerStart'],
                Event::ON_CONNECT => [SharedMemoryServer::class, 'onConnect'],
                Event::ON_RECEIVE => [SharedMemoryServer::class, 'onReceive'],
                Event::ON_CLOSE => [SharedMemoryServer::class, 'onClose'],
            ]
        ]
    ],
    'settings' => [

    ],
    'callbacks' => [
        Event::ON_WORKER_START => function () {

        }
    ],
];