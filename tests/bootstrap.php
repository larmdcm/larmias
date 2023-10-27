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

define('LARMIAS_BASE_PATH', dirname(__DIR__) . '/src');
define('LARMIAS_COMPOSER_FILE', dirname(LARMIAS_BASE_PATH) . '/composer.json');

$container = ApplicationContext::setContainer(Container::getInstance()->bind([
    ApplicationInterface::class => Application::class,
]));

/** @var ApplicationInterface $app */
$app = $container->make(ApplicationInterface::class, ['rootPath' => __DIR__ . '/app']);

$app->discover();