<?php

require '../bootstrap.php';

use Larmias\SharedMemory\Client\Client;
use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';

$kernel = new Kernel($container);

$kernel->setConfig(EngineConfig::build([
    'driver' => \Larmias\Engine\WorkerMan\Driver::class,
    'workers' => [
        [
            'name' => 'process',
            'type' => \Larmias\Engine\WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
                'watch' => [
                    'enabled' => true,
                    'includes' => [
                        __DIR__ . '/client.php'
                    ],
                ],
            ],
            'callbacks' => [
                \Larmias\Engine\Event::ON_WORKER_START => function () {
                    // Client::setEventLoop(\Larmias\Engine\EventLoop::getEvent());
                    // Client::setTimer(\Larmias\Engine\Timer::getTimer());
                    $clients = [];

                    for ($i = 0; $i < 1; $i++) {
                        $client = new Client([
                            'password' => '123456',
                        ]);
                        $clients[] = $client;
                    }

                    $startTime = microtime(true);

                    /**
                     * @var int $k
                     * @var Client $client
                     */
                    foreach ($clients as $k => $client) {
                        for ($i = 0; $i < 100; $i++) {
                            $client->str->set('k_' . $k . '_' . $i, '测试' . $k);
                        }
                    }

                    println(round(microtime(true) - $startTime, 2));
                },
            ]
        ]
    ]
]));

$kernel->run();
