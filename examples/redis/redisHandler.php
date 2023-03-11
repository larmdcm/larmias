<?php

/** @var \Larmias\Contracts\ContainerInterface $container */
$container = require '../di/container.php';


/** @var \Larmias\Contracts\ConfigInterface $config */
$config = $container->get(\Larmias\Contracts\ConfigInterface::class);
$config->load('./redis.php');


$container->bind(\Larmias\Contracts\Redis\RedisFactoryInterface::class, \Larmias\Redis\RedisFactory::class);

$container->bind(\Larmias\Contracts\ContextInterface::class, \Larmias\Engine\Swoole\Context::class);
\Larmias\Engine\Coroutine::init(\Larmias\Engine\Swoole\Coroutine::class);
\Larmias\Engine\Timer::init($container->get(\Larmias\Engine\Swoole\Timer::class));
\Larmias\Engine\Coroutine\Channel::init(\Larmias\Engine\Swoole\Coroutine\Channel::class);

//$container->bind(\Larmias\Contracts\ContextInterface::class, \Larmias\Engine\WorkerMan\Context::class);
//\Larmias\Engine\Timer::init($container->get(\Larmias\Engine\WorkerMan\Timer::class));


/** @var \Larmias\Redis\Redis $redis */
$redis = $container->get(\Larmias\Redis\Redis::class);

return $redis;