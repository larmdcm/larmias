<?php

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Run;
use Larmias\Engine\Timer;
use Larmias\Engine\Swoole\Driver;

require '../bootstrap.php';

/** @var ContainerInterface $container */
$container = require '../di/container.php';

$run = new Run($container);

$run->set(['driver' => Driver::class]);

$run(function (WorkerInterface $worker, KernelInterface $kernel) {
    Timer::tick(1000, function () {
        echo "tick..." . PHP_EOL;
    });

    Timer::after(3000, function () use ($kernel) {
        echo "after..." . PHP_EOL;
        $kernel->stop();
    });
});