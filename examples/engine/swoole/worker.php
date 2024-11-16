<?php

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Engine\Constants;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Run;
use Larmias\Engine\Swoole\Driver;

require '../../bootstrap.php';

/** @var ContainerInterface $container */
$container = require '../../di/container.php';

$run = new Run($container);

$run->set(['driver' => Driver::class, 'settings' => [
    Constants::OPTION_MODE => Constants::MODE_BASE,
    Constants::OPTION_ENABLE_COROUTINE => true,
    Constants::OPTION_MAX_WAIT_TIME => 3,
    Constants::OPTION_LOG_DEBUG => true,
]]);

$run(function (WorkerInterface $worker, KernelInterface $kernel) {
    \Swoole\Coroutine::create(function () {
        sleep(1);
        echo 1 . PHP_EOL;
    });

    \Swoole\Coroutine::create(function () {
        echo 2 . PHP_EOL;
    });

    echo '任务执行中...' . PHP_EOL;
    sleep(5);
    echo '任务执行完成...' . PHP_EOL;
});

