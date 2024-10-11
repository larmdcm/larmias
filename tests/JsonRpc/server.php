<?php

use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\Event;
use Larmias\Engine\Kernel;
use Larmias\Engine\WorkerType;
use Larmias\JsonRpc\Contracts\ServiceCollectorInterface;


/** @var ApplicationInterface $app */
$app = require __DIR__ . '/../app.php';

$kernel = new Kernel($app->getContainer());

$kernel->setConfig(EngineConfig::build([
    'driver' => \Larmias\Engine\WorkerMan\Driver::class,
    'workers' => [
        [
            'name' => 'tcp',
            'type' => WorkerType::TCP_SERVER,
            'host' => '0.0.0.0',
            'port' => 2000,
            'settings' => [
                'worker_num' => 1,
                // \Larmias\Engine\Constants::OPTION_PROTOCOL => \Larmias\Codec\Protocol\FrameProtocol::class,
                \Larmias\Engine\Constants::OPTION_PROTOCOL => \Workerman\Protocols\Frame::class,
            ],
            'callbacks' => [
                Event::ON_RECEIVE => [Larmias\JsonRpc\TcpServer::class, 'onReceive'],
            ]
        ]
    ],
    'settings' => [
    ],
    'callbacks' => [
        Event::ON_WORKER_START => function () use ($app) {
            /** @var ServiceCollectorInterface $serviceCollector */
            $serviceCollector = $app->getContainer()->get(ServiceCollectorInterface::class);
            $serviceCollector->registerService(\LarmiasTest\JsonRpc\Service\UserService::class);
        }
    ],
]));

$kernel->run();