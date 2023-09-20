<?php

use Larmias\Contracts\WebSocket\OnCloseInterface;
use Larmias\Contracts\WebSocket\OnMessageInterface;
use Larmias\Contracts\WebSocket\OnOpenInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\Contracts\PipelineInterface;
use Larmias\Pipeline\Pipeline;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Event\EventDispatcherFactory;
use Larmias\WebSocketServer\Server as WebSocketServer;

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
                Event::ON_OPEN => [WebSocketServer::class, OnOpenInterface::ON_OPEN],
                Event::ON_MESSAGE => [WebSocketServer::class, OnMessageInterface::ON_MESSAGE],
                Event::ON_CLOSE => [WebSocketServer::class, OnCloseInterface::ON_CLOSE],
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
                $serviceProvider = new \Larmias\WebSocketServer\Providers\WebSocketServerServiceProvider($container);
                $serviceProvider->register();
                $serviceProvider->boot();
            }
        ]
    ],
];