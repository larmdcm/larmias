<?php

require '../bootstrap.php';

use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';

$container->bind([
    \Larmias\SharedMemory\Contracts\CommandExecutorInterface::class => \Larmias\SharedMemory\CommandExecutor::class,
    \Larmias\SharedMemory\Contracts\AuthInterface::class => \Larmias\SharedMemory\Auth::class,
]);

$kernel = new Kernel($container);

$kernel->setConfig(EngineConfig::build(require __DIR__ . '/worker.php'));

$kernel->run();