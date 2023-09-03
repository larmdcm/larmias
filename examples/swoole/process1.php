<?php

use Swoole\Process;
use Swoole\Coroutine;
use Swoole\Timer;
use Swoole\Event;
use function Swoole\Coroutine\run;

$process = new Process(function (Process $worker) {
    Process::signal(SIGINT, function ($signal) use ($worker) {
        echo "子进程接收到了信号:" . $signal . PHP_EOL;
        $worker->exit();
    });

    $cid = Coroutine::getCid();
    echo "当前协程cid: {$cid}" . PHP_EOL;
    Timer::tick(1000, function () use ($cid) {
        echo "当前协程 timer cid:" . Coroutine::getCid() . PHP_EOL;
    });

}, false, SOCK_DGRAM, true);

$process->start();

run(function () use ($process) {

    Process::signal(SIGINT, function ($signal) use ($process) {
        echo "主进程接收到了信号:" . $signal . PHP_EOL;
        Process::kill($process->pid, SIGTERM);
        Event::exit();
    });

    Event::add(STDIN, function () {

    });
});

Process::wait();