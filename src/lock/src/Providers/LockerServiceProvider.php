<?php

declare(strict_types=1);

namespace Larmias\Lock\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LockerFactoryInterface;
use Larmias\Contracts\LockerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Lock\Locker;
use Larmias\Lock\LockerFactory;

class LockerServiceProvider implements ServiceProviderInterface
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
        $this->container->bind([
            LockerInterface::class => Locker::class,
            LockerFactoryInterface::class => LockerFactory::class,
        ]);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
    }
}