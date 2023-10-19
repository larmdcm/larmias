<?php

use Swoole\Coroutine;
use function Swoole\Coroutine\run;
use Swoole\Process;
use Swoole\Event;

run(function () {
    Process::signal(SIGINT, function ($signal) {
        echo "主进程接收到了信号:" . $signal . PHP_EOL;
    });
});