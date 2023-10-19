<?php

use Larmias\Di\Container;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Larmias\Event\ListenerProviderFactory;
use Larmias\Event\EventDispatcherFactory;

require '../bootstrap.php';
require './event.php';
$config = require './listeners.php';

$container = Container::getInstance();

$container->bind(ListenerProviderInterface::class,ListenerProviderFactory::make($container,$config));
$container->bind(EventDispatcherInterface::class,EventDispatcherFactory::make($container));

/** @var EventDispatcherInterface $dispatch */
$dispatch = $container->get(EventDispatcherInterface::class);

$dispatch->dispatch(new HelloEvent('hello,world!'));