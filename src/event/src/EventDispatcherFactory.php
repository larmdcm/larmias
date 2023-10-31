<?php

declare(strict_types=1);

namespace Larmias\Event;

use Larmias\Contracts\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;
use Throwable;

class EventDispatcherFactory
{
    /**
     * @param ContainerInterface $container
     * @return EventDispatcherInterface
     * @throws Throwable
     */
    public static function make(ContainerInterface $container): EventDispatcherInterface
    {
        return new EventDispatcher($container->get(ListenerProviderInterface::class));
    }
}