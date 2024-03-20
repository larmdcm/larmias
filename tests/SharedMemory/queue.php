<?php

use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\Event;
use Larmias\Engine\Kernel;

/** @var ApplicationInterface $app */
$app = require __DIR__ . '/../app.php';

$kernel = new Kernel($app->getContainer());

$kernel->setConfig(EngineConfig::build([
    'driver' => \Larmias\Engine\Swoole\Driver::class,
    'workers' => [
        [
            'name' => 'process',
            'type' => \Larmias\Engine\WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_WORKER_START => function () {
                    $client = new Larmias\SharedMemory\Client\Connection(['auto_connect' => true, 'password' => '123456']);
                    var_dump($client->enqueue('test', 'data1'));
                    var_dump($client->qeIsEmpty('test'));
                    var_dump($client->qeCount('test'));
                    var_dump($client->dequeue('test'));
                    var_dump($client->dequeue('test'));
                }
            ]
        ]
    ],
    'settings' => [

    ],
    'callbacks' => [
        Event::ON_WORKER_START => function () {

        }
    ],
]));

$kernel->run();