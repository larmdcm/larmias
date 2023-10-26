<?php

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Engine\Run;
use Larmias\Engine\Swoole\Driver;

require '../bootstrap.php';

/** @var ContainerInterface $container */
$container = require '../di/container.php';

$run = new Run($container);

$run->set([
    'driver' => Driver::class,
    'settings' => [
        'scheduler_mode' => \Larmias\Engine\Constants::SCHEDULER_CO_WORKER,
        'mode' => \Larmias\Engine\Constants::MODE_PROCESS,
    ]
]);

$run(function (WorkerInterface $worker) {
    echo "执行完毕" . $worker->getWorkerId() . PHP_EOL;
    throw new RuntimeException('test123');
});