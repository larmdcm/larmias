<?php

require '../bootstrap.php';

use Larmias\WorkerS\Process\Manager;
use Larmias\WorkerS\Process\Worker\Worker as ProcessWorker;


$manager = new Manager([
    'worker_num' => 1,
]);

$manager->on('workerStart', function (ProcessWorker $worker) {
    $worker->setForceExit(false);
});

$manager->on('worker', function (ProcessWorker $worker) {
    println($worker->getWorkerId());
    sleep(1);
});

$manager->on('workerStop', function () {

});

$manager->on('masterStart', function () {

});

$manager->on('master', function () {

});

$manager->on('masterStop', function () {

});

$manager->on('workerSignal', function (ProcessWorker $worker, int $signal) {
//     dump("worker". $worker->getWorkerId() ."->我收到了退出信号:" . $signal);
});


$manager->run();