<?php

use Larmias\Contracts\Http\OnRequestInterface;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\HttpServer\Server as HttpServer;
use Larmias\Contracts\ConfigInterface;
use Larmias\Config\Config;
use Larmias\HttpServer\Routing\Router;
use Larmias\Contracts\PipelineInterface;
use Larmias\Pipeline\Pipeline;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Event\EventDispatcherFactory;

return [
    'driver' => \Larmias\Engine\WorkerMan\Driver::class,
    'workers' => [
        [
            'name' => 'http',
            'type' => WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9863,
            'settings' => [
                'worker_num' => 2,
            ],
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class, OnRequestInterface::ON_REQUEST]
            ]
        ],
    ],
    'settings' => [
    ],
    'callbacks' => [
        Event::ON_WORKER_START => [
            function (\Larmias\Engine\Contracts\WorkerInterface $worker) {
                $container = require '../di/container.php';
                // $container->bind(ConfigInterface::class, Config::class);
                $container->bind(PipelineInterface::class, Pipeline::class);
                $container->bind(ListenerProviderInterface::class, ListenerProviderFactory::make($container, []));
                $container->bind(EventDispatcherInterface::class, EventDispatcherFactory::make($container));
//                foreach (glob(__DIR__ . '/config/*.php') as $file) {
//                    $container->make(ConfigInterface::class)->load($file);
//                }
                Router::init($container->make(\Larmias\Routing\Router::class));
                Router::get('/', function (\Larmias\HttpServer\Contracts\ResponseInterface $response) {
                    return $response->raw('Hello,World!');
                });
                // require __DIR__ . '/router.php';
            }
        ]
    ],
];