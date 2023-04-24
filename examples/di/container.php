<?php

use Larmias\Di\Container;
use Larmias\Contracts\ConfigInterface;
use Larmias\Config\Config;
use Larmias\Utils\ApplicationContext;

$container = Container::getInstance();
$container->bind(ConfigInterface::class,Config::class);

ApplicationContext::setContainer($container);

return $container;