<?php

require '../bootstrap.php';

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';

$run = new \Larmias\Engine\Run($container);

$run->set(['driver' => \Larmias\Engine\WorkerMan\Driver::class]);

$run(function (\Larmias\Contracts\Worker\WorkerInterface $worker, \Larmias\Engine\Contracts\KernelInterface $kernel) {
    \Larmias\Engine\Timer::tick(1000, function () {
        echo "tick..." . PHP_EOL;
    });
    \Larmias\Engine\Timer::after(3000, function () use ($kernel) {
        echo "after..." . PHP_EOL;
        $kernel->stop();
    });
});