<?php

use Workerman\Worker;

require_once __DIR__ . '/../bootstrap.php';

$httpWorker = new Worker('http://0.0.0.0:9901');

$httpWorker->onMessage = function (\Workerman\Connection\TcpConnection $connection, $request) {
    var_dump($connection->id);
    $connection->send("Hello World");
};

Worker::runAll();