<?php

use Larmias\Config\Config;
use Larmias\Context\ApplicationContext;
use Larmias\Contracts\ConfigInterface;
use Larmias\Di\Container;

$container = Container::getInstance();
$container->bind(ConfigInterface::class,Config::class);

ApplicationContext::setContainer($container);

return $container;