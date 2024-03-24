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
                    $connection = \Larmias\Support\make(\Larmias\SharedMemory\Client\Proxy\Client::class);
                    \Larmias\Engine\Timer::tick(1000, function () use ($connection) {
                        $connection->enqueue('test', (string)mt_rand(100, 666));
                    });

                    $queue1 = new \Larmias\SharedMemory\Client\Queue(['password' => '123456']);
                    $queue1->addConsumer('test', function ($data) {
                        var_dump('1:' . $data);
                    });

//                    $queue2 = new \Larmias\SharedMemory\Client\Queue(['password' => '123456']);
//                    $queue2->addConsumer('test', function ($data) {
//                        var_dump('2:' . $data);
//                    });

                    // $connection->enqueue('test', (string)mt_rand(100, 666));
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