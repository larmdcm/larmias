<?php

use Larmias\Contracts\WebSocket\OnCloseInterface;
use Larmias\Contracts\WebSocket\OnMessageInterface;
use Larmias\Contracts\WebSocket\OnOpenInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\Contracts\PipelineInterface;
use Larmias\Pipeline\Pipeline;
use Larmias\WebSocketServer\Contracts\EventInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Event\EventDispatcherFactory;
use Larmias\WebSocketServer\Server as WebSocketServer;
use Larmias\WebSocketServer\Socket;
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

                /** @var EventInterface $event */
                $event = $container->get(EventInterface::class);
                $event->on(EventInterface::ON_CONNECT, function (Socket $socket, mixed $data) {
                    println('新客户端连接:%s', $socket->getId());
                });

                $event->on('message', function (Socket $socket, mixed $data) {
                    println('接收到客户端消息<%s>:%s', $socket->getId(), (string)$data[0]);
                    $socket->emit('message', 'emit message.');
                });

                $event->on(EventInterface::ON_DISCONNECT, function (Socket $socket, mixed $data) {
                    println('客户端断开连接:%s', $socket->getId());
                });
            }
        ]
    ],
];