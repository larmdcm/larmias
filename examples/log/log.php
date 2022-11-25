<?php

use Larmias\Contracts\ConfigInterface;
use Larmias\Config\Config;
use Larmias\Contracts\LoggerInterface;
use Larmias\Log\Logger;

$container = require '../di/container.php';

$container->bind(ConfigInterface::class,Config::class);
$container->bind(LoggerInterface::class,Logger::class);

$container->get(ConfigInterface::class)->load('./logger.php');


/** @var LoggerInterface $logger */
$logger = $container->get(LoggerInterface::class);

return $logger;