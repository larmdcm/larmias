<?php

declare(strict_types=1);

namespace Larmias\Trace\Providers;

use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Event\Contracts\ListenerInterface;
use Larmias\Trace\Contracts\TraceContextInterface;
use Larmias\Trace\Contracts\TraceInterface;
use Larmias\Trace\Listeners\DatabaseQueryExecutedListener;
use Larmias\Trace\Trace;
use Larmias\Trace\TraceContext;
use Psr\EventDispatcher\ListenerProviderInterface;

class TraceServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function register(): void
    {
        $this->container->bindIf([
            TraceInterface::class => Trace::class,
            TraceContextInterface::class => TraceContext::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        if ($this->container->has(ListenerProviderInterface::class)) {
            $this->listener($this->container->get(ListenerProviderInterface::class));
        }
    }

    /**
     * @param ListenerProviderInterface $listenerProvider
     * @return void
     * @throws \Throwable
     */
    protected function listener(ListenerProviderInterface $listenerProvider): void
    {
        if (!method_exists($listenerProvider, 'on')) {
            return;
        }

        $listeners = [
            DatabaseQueryExecutedListener::class,
        ];

        foreach ($listeners as $listener) {
            $instance = $this->container->get($listener);
            if ($instance instanceof ListenerInterface) {
                foreach ($instance->listen() as $event) {
                    $listenerProvider->on($event, [$instance, 'process']);
                }
            }
        }
    }
}