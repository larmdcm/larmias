<?php

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Engine\Constants;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Run;
use Larmias\Engine\Swoole\Driver;

/** @var ApplicationInterface $app */
$app = require __DIR__ . '/../app.php';

$run = new Run($app->getContainer());

$run->set(['driver' => Driver::class, 'settings' => [
    Constants::OPTION_MODE => Constants::MODE_BASE,
    Constants::OPTION_ENABLE_COROUTINE => true,
    'swoole_max_wait_time' => 30,
]]);

$run(function (WorkerInterface $worker, KernelInterface $kernel) use ($app) {
    \Larmias\SharedMemory\Client\Client::setEventLoop(\Larmias\Engine\EventLoop::getEvent());
    /** @var \Larmias\Contracts\TaskExecutorInterface $executor */
    $executor = $app->getContainer()->get(\Larmias\Contracts\TaskExecutorInterface::class);
    $executor->execute(function () {
        sleep(2);
        var_dump("异步任务1");
        return 1;
    });

    $executor->execute(function () {
        sleep(1);
        var_dump("异步任务2");
        return 2;
    });

    var_dump($executor->syncExecute(function () {
        sleep(3);
        var_dump("sync异步任务3");
        return ['id' => session_create_id()];
    }));
});