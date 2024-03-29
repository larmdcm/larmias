<?php

require '../bootstrap.php';

use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\SharedMemory\Server as SharedMemoryServer;

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';

$container->bind([
    \Larmias\Contracts\ConfigInterface::class => \Larmias\Config\Config::class,
    \Larmias\SharedMemory\Contracts\CommandExecutorInterface::class => \Larmias\SharedMemory\CommandExecutor::class,
    \Larmias\SharedMemory\Contracts\AuthInterface::class => \Larmias\SharedMemory\Auth::class,
    \Larmias\SharedMemory\Contracts\LoggerInterface::class => \Larmias\SharedMemory\Logger::class,
    \Larmias\Contracts\TaskExecutorInterface::class => \Larmias\Task\TaskExecutor::class,
]);

$kernel = new Kernel($container);

$kernel->setConfig(EngineConfig::build([
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
        ],
        [
            'name' => 'taskProcess',
            'type' => WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 2,
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
                \Larmias\SharedMemory\Client\Connection::setEventLoop(\Larmias\Engine\EventLoop::getEvent());
                \Larmias\SharedMemory\Client\Connection::setTimer(\Larmias\Engine\Timer::getTimer());
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
]));

$kernel->run();