<?php

declare(strict_types=1);

namespace Larmias\Lock\Providers;

use Larmias\Contracts\LockerFactoryInterface;
use Larmias\Contracts\LockerInterface;
use Larmias\Lock\Locker;
use Larmias\Lock\LockerFactory;
use Larmias\Framework\ServiceProvider;

class LockerServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf([
            LockerInterface::class => Locker::class,
            LockerFactoryInterface::class => LockerFactory::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/lock.php' => $this->app->getConfigPath() . 'lock.php',
        ]);
    }
}