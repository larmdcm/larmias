<?php

require '../../bootstrap.php';

use Larmias\Engine\Swoole\Process\WorkerPool;
use Larmias\Engine\Swoole\Process\Worker;

$workerPool = new WorkerPool();

$workerPool->set([
    'log_debug' => true,
    'worker_auto_recover' => true,
    'enable_coroutine' => false,
    'max_wait_time' => 3,
]);

class Obj1
{
    public function __destruct()
    {
        var_dump(__FUNCTION__);
    }
}

$workerPool->on('workerStart', function (Worker $worker) {
    $obj = new Obj1();
    echo "进程启动 #{$worker->getId()} -> {$worker->getPid()}" . PHP_EOL;
//    var_dump(\Swoole\Coroutine::getCid());
    // \Larmias\Engine\Swoole\ProcessManager::isRunning()
    while (true) {
        sleep(1);
    }
});

$workerPool->start();

echo "运行结束" . PHP_EOL;