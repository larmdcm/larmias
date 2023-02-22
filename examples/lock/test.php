<?php

require '../bootstrap.php';

$container = require '../di/container.php';
$container->get(\Larmias\Contracts\ConfigInterface::class)->load('../redis/redis.php');
$container->bind(\Larmias\Contracts\Redis\RedisFactoryInterface::class, \Larmias\Redis\RedisFactory::class);
$container->bind(\Larmias\Contracts\LockerInterface::class, \Larmias\Lock\Locker::class);
$container->bind(\Larmias\Contracts\LockerFactoryInterface::class, \Larmias\Lock\LockerFactory::class);

\Larmias\Lock\LockUtils::acquire('lock', function () {
    sleep(10);
    println('执行完成.');
});

