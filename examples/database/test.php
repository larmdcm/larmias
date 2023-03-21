<?php

use function Swoole\Coroutine\run;

run(function () {
    require '../bootstrap.php';
    /** @var \Larmias\Contracts\ContainerInterface $container */
    $container = require '../di/container.php';
    $container->bind(\Larmias\Contracts\ContextInterface::class, \Larmias\Engine\Swoole\Context::class);
    \Larmias\Engine\Coroutine::init(\Larmias\Engine\Swoole\Coroutine::class);
    \Larmias\Engine\Timer::init($container->get(\Larmias\Engine\Swoole\Timer::class));
    \Larmias\Engine\Coroutine\Channel::init(\Larmias\Engine\Swoole\Coroutine\Channel::class);

    $manager = new \Larmias\Database\Manager(require __DIR__ . '/database.php');

    $connection = $manager->createConnection();

    var_dump($connection->query('select 1'));
});