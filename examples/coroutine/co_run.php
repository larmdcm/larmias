<?php

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Engine\Run;
use Larmias\Engine\Swoole\Driver;
use Larmias\Coroutine\Coroutine;
use function Larmias\Coroutine\go;
use function Larmias\Coroutine\defer;
use function Larmias\Coroutine\channel;

require '../bootstrap.php';

/** @var ContainerInterface $container */
$container = require '../di/container.php';

\Larmias\Facade\AbstractFacade::setContainer($container);

$run = new Run($container);

$run->set([
    'driver' => Driver::class,
    'settings' => [
        'mode' => \Larmias\Engine\Constants::MODE_WORKER,
    ]
]);

$run(function (WorkerInterface $worker) {
    $channel = channel(2);

    defer(function () {
        echo "结束时执行..." . PHP_EOL;
    });

    go(function () use ($channel) {
        sleep(2);
        echo Coroutine::id() . PHP_EOL;
        $channel->push(true);
    });

    go(function () use ($channel) {
        sleep(1);
        echo Coroutine::id() . PHP_EOL;
        $channel->push(true);
    });

    $channel->pop();
    $channel->pop();
});