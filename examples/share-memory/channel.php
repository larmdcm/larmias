<?php

require '../bootstrap.php';

use Larmias\ShareMemory\Client\Client;
use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';

$kernel = new Kernel($container);

$kernel->setConfig(EngineConfig::build([
    'driver' => \Larmias\Engine\WorkerMan\Driver::class,
    'workers' => [
        [
            'name' => 'watcherProcess',
            'type' => \Larmias\Engine\WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
                'watch' => [
                    'enabled' => true,
                    'includes' => [

                    ],
                ],
            ],
            'callbacks' => [
                \Larmias\Engine\Event::ON_WORKER_START => function () {
                    $client = new Client();
                    $client->auth('123456');
                    $client->channel->subscribe(['chat','public'],function ($data) {
                        var_dump($data);
                    });
                },
            ]
        ]
    ]
]));

$kernel->run();