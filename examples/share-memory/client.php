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
                    var_dump($client->command('auth', ['123456']));
                    var_dump($client->command('select', ['map']));
                    var_dump($client->command('map:set', ['name', '测试']));
                    var_dump($client->command('map:get', ['name']));
                },
            ]
        ]
    ]
]));

$kernel->run();
