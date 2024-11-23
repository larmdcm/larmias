<?php


use Swoole\Coroutine;

$http = new Swoole\Http\Server('0.0.0.0', 9901);

$http->set(['enable_coroutine' => true]);

$http->on('Request', function (\Swoole\Http\Request $request, \Swoole\Http\Response $response) {
    var_dump(date('Y-m-d H:i:s') . '接收到request:' . Coroutine::getCid() . '---' . $request->streamId . '---' . $request->fd);
    $response->header('Content-Type', 'text/html; charset=utf-8');
    $response->end('hello,world!');
});

$http->start();