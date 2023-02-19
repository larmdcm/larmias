<?php

require '../bootstrap.php';

use Larmias\WorkerS\Server;

use Larmias\WorkerS\Protocols\Http\{Request,Response};
use Larmias\WorkerS\Worker;
use Larmias\WorkerS\Manager;
use Larmias\WorkerS\Process\Worker\Worker as ProcessWorker;

$worker = new Worker();

$worker->setConfig([
    'task_worker_num' => 1
]);

$worker->on('worker',function (ProcessWorker $processWorker) use ($worker) {
//    println($processWorker->getWorkerId());
//    $worker->task(function () {
//        println("异步任务执行了2");
//        sleep(3);
//    });
//    sleep(1);
});

$httpServer = new Server("http://0.0.0.0:9863");

$httpServer->setConfig([
    'task_worker_num' => 1,
]);

$httpServer->on('request',function (Request $request,Response $response) {
    // $response->write("<h1>Hello,World!</h1>");
    $response->end('<h1>Hello,World!</h1>');
});

Manager::runAll();