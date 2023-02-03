<?php

require '../bootstrap.php';

use Larmias\Engine\Kernel;
use Larmias\Engine\EngineConfig;

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';

$container->bind([
    \Larmias\ShareMemory\Contracts\CommandExecutorInterface::class => \Larmias\ShareMemory\CommandExecutor::class,
    \Larmias\ShareMemory\Contracts\AuthInterface::class => \Larmias\ShareMemory\Auth::class,
]);

$kernel = new Kernel($container);

$kernel->setConfig(EngineConfig::build(require __DIR__ . '/worker.php'));

$kernel->run();