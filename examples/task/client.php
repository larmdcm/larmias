<?php

require '../bootstrap.php';

use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';

$kernel = new Kernel($container);

$container->bind([
    \Larmias\Contracts\ConfigInterface::class => \Larmias\Config\Config::class,
    \Larmias\Contracts\TaskExecutorInterface::class => \Larmias\Task\TaskExecutor::class,
]);

$container->make(\Larmias\Contracts\ConfigInterface::class)->set('task', [
    'host' => '127.0.0.1',
    'port' => 2000,
    'password' => '123456',
]);

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
                \Larmias\Engine\Event::ON_WORKER_START => function () use ($container) {
                    \Larmias\SharedMemory\Client\Client::setEventLoop(\Larmias\Engine\EventLoop::getEvent());
                    /** @var \Larmias\Contracts\TaskExecutorInterface $executor */
                    $executor = $container->make(\Larmias\Contracts\TaskExecutorInterface::class);
                    $executor->execute(function () {
                        sleep(2);
                        var_dump("异步任务1");
                        return 1;
                    });

                    $executor->execute(function () {
                        sleep(1);
                        var_dump("异步任务2");
                        return 2;
                    });

                    var_dump($executor->syncExecute(function () {
                        sleep(3);
                        var_dump("sync异步任务3");
                        return ['id' => session_create_id()];
                    }));
                },
            ]
        ]
    ]
]));

$kernel->run();
