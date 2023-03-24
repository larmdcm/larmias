<?php

use Larmias\Di\Container;

$container = Container::getInstance();

\Larmias\Engine\Coroutine::init(\Larmias\Engine\Swoole\Coroutine::class);
\Larmias\Engine\Coroutine\Channel::init(\Larmias\Engine\Swoole\Coroutine\Channel::class);
\Larmias\Engine\Timer::init($container->get(\Larmias\Engine\Swoole\Timer::class));

$container->bind(\Larmias\Contracts\ContextInterface::class, \Larmias\Engine\Swoole\Context::class);
$container->bind(\Larmias\Contracts\Coroutine\CoroutineFactoryInterface::class, \Larmias\Engine\Factory\CoroutineFactory::class);
$container->bind(\Larmias\Contracts\Coroutine\ChannelFactoryInterface::class, \Larmias\Engine\Factory\ChannelFactory::class);
$container->bind(\Larmias\Contracts\TimerInterface::class, \Larmias\Engine\Timer::getTimer());


return $container;