<?php

require '../bootstrap.php';
$container = require __DIR__ . '/init.php';

$run = new \Larmias\Engine\Run($container);

$run->set(['driver' => \Larmias\Engine\WorkerMan\Driver::class]);

$run([\Larmias\AsyncQueue\Process\ConsumerProcess::class, 'handle']);