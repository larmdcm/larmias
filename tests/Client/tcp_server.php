<?php

use Larmias\Contracts\ApplicationInterface;
use Larmias\Engine\EngineConfig;
use Larmias\Engine\Event;
use Larmias\Engine\Kernel;
use Larmias\Engine\WorkerType;
use Larmias\Engine\Constants;
use Larmias\Contracts\Tcp\ConnectionInterface;
use function Larmias\Support\println;

/** @var ApplicationInterface $app */
$app = require __DIR__ . '/../app.php';

$kernel = new Kernel($app->getContainer());

$kernel->setConfig(EngineConfig::build([
    'driver' => \Larmias\Engine\Swoole\Driver::class,
    'workers' => [
        [
            'name' => 'tcp',
            'type' => WorkerType::TCP_SERVER,
            'host' => '0.0.0.0',
            'port' => 2000,
            'settings' => [
                Constants::OPTION_WORKER_NUM => 1,
            ],
            'callbacks' => [
                Event::ON_CONNECT => function (ConnectionInterface $connection) {
                    println('【%d】客户端连接', $connection->getId());
                    \Larmias\Engine\Timer::tick(1000, fn() => $connection->send('hello'));
                    \Larmias\Engine\Timer::tick(2000, fn() => $connection->send('world'));
                },
                Event::ON_RECEIVE => function (ConnectionInterface $connection, mixed $data) {
                    println('【%d】客户端消息：%s', $connection->getId(), $data);
                    $connection->send('world');
                    // \Larmias\Engine\Timer::tick(1000, fn() => $connection->send('!'));
                },
                Event::ON_CLOSE => function (ConnectionInterface $connection) {
                    println('【%d】新客户端关闭连接', $connection->getId());
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