<?php

use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;

require '../bootstrap.php';
$container = require __DIR__ . '/init.php';

$kernel = new Kernel($container);

$kernel->setConfig(EngineConfig::build([
    'driver' => \Larmias\Engine\WorkerMan\Driver::class,
    'workers' => [
        [
            'name' => 'taskProcess',
            'type' => \Larmias\Engine\WorkerType::WORKER_PROCESS,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                \Larmias\Engine\Event::ON_WORKER_START => [\Larmias\AsyncQueue\Process\ConsumerProcess::class, 'handle'],
            ]
        ],
    ]
]));

$kernel->run();