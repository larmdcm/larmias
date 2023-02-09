<?php

declare(strict_types=1);

namespace Larmias\Event;

use Larmias\Event\Contracts\ListenerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventDispatcher\ListenerProviderInterface;

class ListenerProviderFactory
{
    /**
     * @param ContainerInterface $container
     * @param array $listeners
     * @return ListenerProviderInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function make(ContainerInterface $container, array $listeners = []): ListenerProviderInterface
    {
        $provider = new ListenerProvider();
        
        foreach ($listeners as $listener => $priority) {
            if (is_int($listener)) {
                $listener = $priority;
                $priority = 1;
            }
            if (is_string($listener)) {
                static::register($provider, $container, $listener, $priority);
            }
        }

        return $provider;
    }

    /**
     * @param ListenerProviderInterface $provider
     * @param ContainerInterface $container
     * @param string $listener
     * @param int $priority
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function register(ListenerProviderInterface $provider, ContainerInterface $container, string $listener, int $priority = 1): void
    {
        $instance = $container->get($listener);
        if ($instance instanceof ListenerInterface) {
            foreach ($instance->listen() as $event) {
                $provider->on($event, [$instance, 'process'], $priority);
            }
        }
    }
}