<?php

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';


/** @var \Larmias\Contracts\ConfigInterface $config */
$config = $container->get(\Larmias\Contracts\ConfigInterface::class);
$config->load('./redis.php');


$container->bind(\Larmias\Contracts\Redis\RedisFactoryInterface::class, \Larmias\Redis\RedisFactory::class);

/** @var \Larmias\Redis\Redis $redis */
$redis = $container->get(\Larmias\Redis\Redis::class);

return $redis;