<?php

require '../../bootstrap.php';

use Larmias\Engine\Swoole\Process\WorkerPool;
use Larmias\Engine\Swoole\Process\Worker;

$workerPool = new WorkerPool();

$workerPool->on('workerStart', function (Worker $worker) {
    echo "进程启动 #{$worker->getId()} -> {$worker->getPid()}" . PHP_EOL;
    while (true) {
        sleep(1);
    }
});

$workerPool->start();

echo "运行结束" . PHP_EOL;