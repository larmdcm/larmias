<?php

declare(strict_types=1);

namespace Larmias\Event;

use Larmias\Contracts\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class EventDispatcherFactory
{
    /**
     * @param ContainerInterface $container
     * @return EventDispatcherInterface
     * @throws \Psr\Container\ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public static function make(ContainerInterface $container): EventDispatcherInterface
    {
        return new EventDispatcher($container->get(ListenerProviderInterface::class));
    }
}