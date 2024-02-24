<?php

declare(strict_types=1);

namespace Larmias\Coroutine\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\Sync\LockerInterface;
use Larmias\Contracts\Sync\WaiterInterface;
use Larmias\Contracts\Sync\WaitGroupInterface;
use Larmias\Contracts\Concurrent\ConcurrentInterface;
use Larmias\Contracts\Concurrent\ParallelInterface;
use Larmias\Coroutine\Concurrent\Concurrent;
use Larmias\Coroutine\Concurrent\Parallel;
use Larmias\Coroutine\Sync\Locker;
use Larmias\Coroutine\Sync\Waiter;
use Larmias\Coroutine\Sync\WaitGroup;

class CoroutineServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf([
            ConcurrentInterface::class => Concurrent::class,
            ParallelInterface::class => Parallel::class,
            WaitGroupInterface::class => WaitGroup::class,
            WaiterInterface::class => Waiter::class,
            LockerInterface::class => Locker::class,
        ]);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
    }
}