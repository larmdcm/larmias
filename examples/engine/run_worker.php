<?php

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Engine\Run;
use Larmias\Engine\WorkerMan\Driver;
use Larmias\Engine\Timer;

require '../bootstrap.php';

/** @var ContainerInterface $container */
$container = require '../di/container.php';

$run = new Run($container);

$run->set([
    'driver' => Driver::class,
    'settings' => [
        'mode' => \Larmias\Engine\Constants::MODE_WORKER,
    ]
]);

$run(function (WorkerInterface $worker) {
    echo "执行完毕" . $worker->getWorkerId() . PHP_EOL;
    $count = 1;
    \Larmias\Engine\Timer::tick(1000, function () use (&$count) {
        echo "tick ing..." . PHP_EOL;
        $count++;
        if ($count > 3) {
            Timer::clear();
        }
    });
});