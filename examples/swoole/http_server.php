<?php

use Swoole\Coroutine;
use function Swoole\Coroutine\run;
use Swoole\Coroutine\Http\Server as HttpServer;

run(function () {
    $server = new HttpServer('0.0.0.0', 9901, false, true);
    $server->set([
//        'open_tcp_keepalive' => true,
//        'tcp_keepidle' => 1,
//        'tcp_keepinterval' => 1,
//        'tcp_keepcount' => 1,
    ]);

    $server->handle('/', function ($req, \Swoole\Http\Response $resp) {
        var_dump(date('Y-m-d H:i:s') . '接收到request:' . Coroutine::getCid());
        // $resp->header('connection', 'close');
        $resp->end('hello,world!');
    });

    $server->start();
});