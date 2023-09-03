<?php

use Swoole\Coroutine;
use function Swoole\Coroutine\run;

run(function () {
    Coroutine::create(function () {
        sleep(1);
        echo 1 . PHP_EOL;
    });

    Coroutine::create(function () {
        echo 2 . PHP_EOL;
    });
});

echo 3 . PHP_EOL;