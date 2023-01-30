<?php

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
                'worker_num' => 1,
                'task_worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_REQUEST => [HttpServer::class, HttpServer::ON_REQUEST]
            ]
        ],
        [
            'name' => 'watcherProcess',
            'type' => WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
                'watch' => [
                    'enabled' => true,
                    'includes' => [
                        __DIR__ . '/config',
                        __DIR__ . '/router.php',
                    ],
                ],
            ],
            'callbacks' => [
                Event::ON_WORKER_START => [\Larmias\Engine\Process\Handler\WorkerHotUpdateHandler::class, 'handle'],
            ]
        ]
    ],
    'settings' => [

    ],
    'callbacks' => [
        Event::ON_WORKER_START => function () {
            $container = require '../di/container.php';
            $container->bind(ConfigInterface::class, Config::class);
            $container->bind(PipelineInterface::class, Pipeline::class);
            $container->bind(ListenerProviderInterface::class, ListenerProviderFactory::make($container, []));
            $container->bind(EventDispatcherInterface::class, EventDispatcherFactory::make($container));
            $container->bind(\Larmias\Contracts\Redis\RedisFactoryInterface::class, \Larmias\Redis\RedisFactory::class);
            $container->bind(\Larmias\Contracts\SessionInterface::class, \Larmias\Session\Session::class);
            $container->bind(\Larmias\Contracts\ViewInterface::class, \Larmias\View\View::class);
            $container->bind(\Larmias\Http\CSRF\Contracts\CsrfManagerInterface::class, \Larmias\Http\CSRF\CsrfManager::class);
            foreach (glob(__DIR__ . '/config/*.php') as $file) {
                $container->make(ConfigInterface::class)->load($file);
            }
            Router::init($container->make(\Larmias\Routing\Router::class));
            require __DIR__ . '/router.php';
        }
    ],
];