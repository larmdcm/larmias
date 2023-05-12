<?php

/** @var Container $container */

/** @var QueueInterface $queue */

use Larmias\Di\Container;
use Larmias\AsyncQueue\Contracts\QueueInterface;

require '../bootstrap.php';

$container = require __DIR__ . '/init.php';


$kernel = new \Larmias\Engine\Kernel($container);

$kernel->setConfig(\Larmias\Engine\EngineConfig::build([
    'driver' => \Larmias\Engine\WorkerMan\Driver::class,
    'workers' => [
        [
            'name' => 'queueTestProcess',
            'type' => \Larmias\Engine\WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                \Larmias\Engine\Event::ON_WORKER_START => function () use ($container) {
                    /** @var QueueInterface $queue */
                    $queue = $container->get(QueueInterface::class);

                    $queue->push(new ExampleJob(), ['name' => 'push']);
                    $queue->push(new ExampleJob(), ['name' => 'delay'], 5000);

                    var_dump($queue->driver()->status());
                }
            ]
        ],
    ]
]));

$kernel->run();