<?php

require '../bootstrap.php';

$container = require '../di/container.php';
$container->get(\Larmias\Contracts\ConfigInterface::class)->load('../redis/redis.php');
$container->bind(\Larmias\Contracts\Redis\RedisFactoryInterface::class, \Larmias\Redis\RedisFactory::class);

\Larmias\Lock\Locker::create('lock', function () {
    sleep(10);
    println('执行完成.');
})->acquire();

