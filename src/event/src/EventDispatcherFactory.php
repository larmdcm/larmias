<?php

declare(strict_types=1);

namespace Larmias\Event;

use Larmias\Contracts\ContainerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcherFactory
{
    /**
     * @param ContainerInterface $container
     * @return EventDispatcherInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function make(ContainerInterface $container): EventDispatcherInterface
    {
        return new EventDispatcher($container->get(ListenerProviderInterface::class));
    }
}