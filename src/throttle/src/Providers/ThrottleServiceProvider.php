<?php

declare(strict_types=1);

namespace Larmias\Throttle\Providers;

use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ThrottleInterface;
use Larmias\Throttle\Throttle;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;

class ThrottleServiceProvider implements ServiceProviderInterface
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
        $this->container->bindIf(ThrottleInterface::class, Throttle::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/auth.php' => $app->getConfigPath() . 'auth.php',
            ]);
        }
    }
}