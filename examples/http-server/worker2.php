<?php

use Larmias\Contracts\Http\OnRequestInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\HttpServer\Server as HttpServer;
use Larmias\HttpServer\Routing\Router;
use Larmias\Contracts\PipelineInterface;
use Larmias\Pipeline\Pipeline;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Event\EventDispatcherFactory;

return [
    'driver' => \Larmias\Engine\Swoole\Driver::class,
    'workers' => [
        [
            'name' => 'http',
            'type' => WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9863,
            'settings' => [
                'worker_num' => \Larmias\Support\get_cpu_num(),
            ],
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class, OnRequestInterface::ON_REQUEST]
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

                $providerList = [
                    \Larmias\ExceptionHandler\Providers\ExceptionHandlerServiceProvider::class,
                    \Larmias\HttpServer\Providers\HttpServiceProvider::class,
                ];

                foreach ($providerList as $provider) {
                    $serviceProvider = new $provider($container);
                    $serviceProvider->register();
                }

                Router::get('/', function () {
                    return 'Hello,World!';
                });

                Router::get('/favicon.ico', function () {
                    return '';
                });
            }
        ]
    ],
];