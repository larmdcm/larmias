<?php

/** @var Container $container */

/** @var QueueInterface $queue */

use Larmias\Di\Container;
use Larmias\AsyncQueue\Contracts\QueueInterface;

require '../bootstrap.php';

$container = require __DIR__ . '/init.php';
$run = new \Larmias\Engine\Run($container);

$run->set(['driver' => \Larmias\Engine\WorkerMan\Driver::class]);

$run(function (\Larmias\Contracts\Worker\WorkerInterface $worker, \Larmias\Engine\Contracts\KernelInterface $kernel) use ($container) {
    /** @var QueueInterface $queue */
    $queue = $container->get(QueueInterface::class);

    $queue->push(new ExampleJob(), ['name' => 'push']);
    $queue->push(new ExampleJob(), ['name' => 'delay'], 5000);

    var_dump($queue->driver()->status());

    $kernel->stop();
});