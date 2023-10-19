<?php

use Swoole\Coroutine;
use function Swoole\Coroutine\run;
use Swoole\Coroutine\Http\Server as HttpServer;
use Swoole\Process;
use Swoole\Coroutine\Channel;

run(function () {

    $exitChan = ['http' => new Channel(), 'ws' => new Channel()];

    Process::signal(SIGINT, function ($signal) use ($exitChan) {
        echo "主进程接收到了信号:" . $signal . PHP_EOL;
        foreach ($exitChan as $chan) {
            $chan->close();
        }
    });

    Coroutine::create(function () use ($exitChan) {
        $server = new HttpServer('0.0.0.0', 9501, false, false);

        $server->handle('/', function ($req, $resp) {
            $resp->end('hello,world!');
        });

        Coroutine::create(function () use ($server, $exitChan) {
            $exitChan['http']->pop();
            $server->shutdown();
        });

        $server->start();
    });

    Coroutine::create(function () use ($exitChan) {
        $server = new HttpServer('0.0.0.0', 9502, false, false);

        $server->handle('/', function ($req, $resp) {
            $resp->upgrade();
        });

        Coroutine::create(function () use ($server, $exitChan) {
            $exitChan['ws']->pop();
            $server->shutdown();
        });

        $server->start();
    });
});