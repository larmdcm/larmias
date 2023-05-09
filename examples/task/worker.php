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
                'protocol' => \Workerman\Protocols\Frame::class,
                'packer' => \Larmias\Engine\Swoole\Packer\FramePacker::class,
            ],
            'callbacks' => [
                Event::ON_WORKER_START => [SharedMemoryServer::class, 'onWorkerStart'],
                Event::ON_CONNECT => [SharedMemoryServer::class, 'onConnect'],
                Event::ON_RECEIVE => [SharedMemoryServer::class, 'onReceive'],
                Event::ON_CLOSE => [SharedMemoryServer::class, 'onClose'],
            ]
        ],
        [
            'name' => 'taskProcess',
            'type' => WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_WORKER_START => [\Larmias\Task\Process\TaskProcess::class, 'handle'],
            ]
        ],
    ],
    'settings' => [

    ],
    'callbacks' => [
        Event::ON_WORKER_START => [
            function () {
                \Larmias\SharedMemory\Client\Client::setEventLoop(\Larmias\Engine\EventLoop::getEvent());
                $container = require '../di/container.php';
                $container->make(\Larmias\Contracts\ConfigInterface::class)->set('task', [
                    'host' => '127.0.0.1',
                    'port' => 2000,
                    'password' => '123456',
                ]);
                $container->get(\Larmias\SharedMemory\Contracts\CommandExecutorInterface::class)->addCommand(
                    \Larmias\Task\Command\TaskCommand::COMMAND_NAME, \Larmias\Task\Command\TaskCommand::class,
                );
            }
        ]
    ],
];