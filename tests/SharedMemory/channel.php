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
                    $client = new Larmias\SharedMemory\Client\Client(['auto_connect' => false, 'password' => '123456']);
                    $client->channel->subscribe(['chat', 'public'], function ($data) {
                        var_dump($data);
                    });

                    $client->channel->publish('chat', 'hello chat');
                    $client->channel->publish('public', 'hello public');

                    $client->channel->channels(function ($data) {
                        var_dump($data);
                    });
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