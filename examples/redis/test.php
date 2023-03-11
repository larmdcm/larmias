<?php

use function Swoole\Coroutine\run;

run(function () {
    require '../bootstrap.php';

    /** @var \Larmias\Redis\Redis $redis */
    $redis = require './redisHandler.php';

    $redis->set('redis', 1);
    var_dump($redis->get('redis'));
});