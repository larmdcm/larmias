<?php

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Http\RequestInterface;
use Larmias\Contracts\Http\ResponseInterface;
use Larmias\Contracts\WebSocket\ConnectionInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Engine\Event;
use Larmias\Engine\Timer;
use Larmias\Engine\Swoole\Driver;

require '../bootstrap.php';

/** @var ContainerInterface $container */
$container = require '../di/container.php';

$kernel = new \Larmias\Engine\Kernel($container);

$kernel->setConfig(\Larmias\Engine\EngineConfig::build(config: [
    'driver' => Driver::class,
    'workers' => [
        [
            'name' => 'http',
            'type' => \Larmias\Engine\WorkerType::HTTP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9501,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_REQUEST => function (RequestInterface $req, ResponseInterface $resp) {
                    $resp->end('hello,world!');
                }
            ]
        ],
        [
            'name' => 'websocket',
            'type' => \Larmias\Engine\WorkerType::WEBSOCKET_SERVER,
            'host' => '0.0.0.0',
            'port' => 9502,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_OPEN => function (ConnectionInterface $connection) {
                    echo $connection->getId() . PHP_EOL;
                }
            ]
        ],
        [
            'name' => 'process',
            'type' => \Larmias\Engine\WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_WORKER_START => function (WorkerInterface $worker) {
                    Timer::tick(1000, function () {
                        echo 'tick...' . PHP_EOL;
                    });
                }
            ]
        ]
    ],
    'callbacks' => [
        Event::ON_WORKER_START => function (WorkerInterface $worker) {
            echo 'worker ' . $worker->getWorkerId() . ' started.' . PHP_EOL;
        }
    ],
    'settings' => [
        'mode' => \Larmias\Engine\Constants::SCHEDULER_CO_WORKER,
    ],
]));

$kernel->run();