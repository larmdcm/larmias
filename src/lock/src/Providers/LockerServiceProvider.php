<?php

declare(strict_types=1);

namespace Larmias\Lock\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LockerFactoryInterface;
use Larmias\Contracts\LockerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Lock\Locker;
use Larmias\Lock\LockerFactory;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;

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
        $this->container->bindIf([
            LockerInterface::class => Locker::class,
            LockerFactoryInterface::class => LockerFactory::class,
        ]);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/lock.php' => $app->getConfigPath() . 'lock.php',
            ]);
        }
    }
}