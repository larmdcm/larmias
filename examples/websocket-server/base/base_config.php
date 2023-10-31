<?php

use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\Contracts\PipelineInterface;
use Larmias\Pipeline\Pipeline;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Event\EventDispatcherFactory;
use Larmias\Contracts\WebSocket\ConnectionInterface;
use Larmias\Contracts\WebSocket\FrameInterface;
use function Larmias\Support\println;

return [
    'driver' => \Larmias\Engine\Swoole\Driver::class,
    'workers' => [
        [
            'name' => 'websocket',
            'type' => WorkerType::WEBSOCKET_SERVER,
            'host' => '0.0.0.0',
            'port' => 9602,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_OPEN => function (ConnectionInterface $connection) {
                    println("有客户端连接:id = %d", $connection->getId());
                },
                Event::ON_MESSAGE => function (ConnectionInterface $connection, FrameInterface $frame) {
                    var_dump($connection->getRequest()->query('id'));
                    println("有客户端发送了消息:id = %d,message = %s", $connection->getId(), $frame->getData());
                },
                Event::ON_CLOSE => function (ConnectionInterface $connection) {
                    println("有客户端关闭连接:id = %d", $connection->getId());
                },
            ]
        ],
    ],
    'settings' => [],
    'callbacks' => [
        Event::ON_WORKER_START => [
            function (WorkerInterface $worker) {
                $container = require '../di/container.php';
                $container->bind(PipelineInterface::class, Pipeline::class);
                $container->bind(ListenerProviderInterface::class, ListenerProviderFactory::make($container, []));
                $container->bind(EventDispatcherInterface::class, EventDispatcherFactory::make($container));
            }
        ]
    ],
];