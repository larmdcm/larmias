<?php

use Larmias\Di\Container;
use Larmias\Contracts\ConfigInterface;
use Larmias\Config\Config;

$container = Container::getInstance();
$container->bind(ConfigInterface::class,Config::class);

return $container;