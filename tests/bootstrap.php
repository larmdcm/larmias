<?php

declare(strict_types=1);

use Larmias\Contracts\ApplicationInterface;
use Larmias\Di\Container;
use Larmias\Framework\Application;
use Larmias\Utils\ApplicationContext;

ini_set('display_errors', 'on');
ini_set('display_startup_errors', 'on');
ini_set('memory_limit', '1G');

error_reporting(E_ALL);

$container = ApplicationContext::setContainer(Container::getInstance()->bind([
    ApplicationInterface::class => Application::class,
]));

/** @var ApplicationInterface $app */
$app = $container->make(ApplicationInterface::class, ['rootPath' => __DIR__ . '/app']);

$app->discover();