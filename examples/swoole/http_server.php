<?php

use Swoole\Coroutine;
use function Swoole\Coroutine\run;
use Swoole\Coroutine\Http\Server as HttpServer;

run(function () {

    $server = new HttpServer('0.0.0.0', 9501, false, true);

    $server->handle('/', function ($req, $resp) {
        var_dump(Coroutine::getContext());
        $resp->end('hello,world!');
    });

    $server->start();
});