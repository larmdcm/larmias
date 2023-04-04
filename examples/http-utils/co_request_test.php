<?php

use Larmias\Http\Utils\Request\Client;
use function Swoole\Coroutine\run;
use Swoole\Runtime;

run(function () {
    Runtime::enableCoroutine();

    require '../bootstrap.php';

    go(function () {
        $client = new Client();

        $response = $client->get('https://www.baidu.com');

        dump($response->getBody()->getContents());
    });

    go(function () {
        echo "1\n";
    });
});