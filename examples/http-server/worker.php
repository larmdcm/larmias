<?php

use Larmias\Engine\DriverConfigManager;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Event;
use Larmias\HttpServer\Server as HttpServer;
use Larmias\Contracts\ConfigInterface;
use Larmias\Config\Config;
use Larmias\HttpServer\Routing\Router;
use Larmias\Contracts\PipelineInterface;
use Larmias\Pipeline\Pipeline;

return [
    'driver' => DriverConfigManager::WORKER_S,
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
                Event::ON_REQUEST => [HttpServer::class,HttpServer::ON_REQUEST]
            ]
        ]
    ],
    'settings'  => [],
    'callbacks' => [
        Event::ON_WORKER_START => function () {
            $container = require '../di/container.php';
            $container->bind(ConfigInterface::class,Config::class);
            $container->bind(PipelineInterface::class,Pipeline::class);
            foreach (glob(__DIR__ . '/config/*.php') as $file) {
                $container->make(ConfigInterface::class)->load($file);
            }
            Router::init($container);
            require __DIR__ . '/router.php';
        }
    ],
];