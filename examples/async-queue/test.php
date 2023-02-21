<?php

/** @var Container $container */

/** @var QueueInterface $queue */

use Larmias\Di\Container;
use Larmias\AsyncQueue\Contracts\QueueInterface;


require '../bootstrap.php';
$container = require __DIR__ . '/init.php';

$queue = $container->get(QueueInterface::class);

$queue->push(new ExampleJob(), ['name' => 'test']);
//$queue->consumer();

var_dump($queue->driver()->status());