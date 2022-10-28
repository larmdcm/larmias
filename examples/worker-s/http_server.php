<?php

require '../bootstrap.php';

use Larmias\WorkerS\Server;

use Larmias\WorkerS\Protocols\Http\{Request,Response};

$httpServer = new Server("http://0.0.0.0:9863");

$httpServer->on('request',function (Request $request,Response $response) {
    $response->write("<h1>Hello,World!</h1>");
    $response->end();
});

$httpServer->start();