<?php

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Engine\Event;
use Larmias\Engine\Swoole\Driver;
use Larmias\Engine\Constants;

require '../../bootstrap.php';

/** @var ContainerInterface $container */
$container = require '../../di/container.php';

$kernel = new \Larmias\Engine\Kernel($container);

$kernel->setConfig(\Larmias\Engine\EngineConfig::build(config: [
    'driver' => Driver::class,
    'workers' => [
        [
            'name' => 'tcp',
            'type' => \Larmias\Engine\WorkerType::TCP_SERVER,
            'host' => '0.0.0.0',
            'port' => 9601,
            'settings' => [
                'worker_num' => 1,
            ],
            'callbacks' => [
                Event::ON_RECEIVE => function (\Larmias\Contracts\Tcp\ConnectionInterface $connection, mixed $data) {
                    var_dump($data);
                    println('耗时任务 start');
                    sleep(2);
                    println('耗时任务 end');
                    throw new RuntimeException('error');
                }
            ]
        ],
    ],
    'callbacks' => [
        Event::ON_WORKER_START => function (WorkerInterface $worker) {
            echo 'worker ' . $worker->getWorkerId() . ' started.' . PHP_EOL;
        }
    ],
    'settings' => [
        Constants::OPTION_DAEMONIZE => false,
        Constants::OPTION_LOG_DEBUG => true,
        Constants::OPTION_WORKER_AUTO_RECOVER => true,
    ],
]));

$kernel->run();