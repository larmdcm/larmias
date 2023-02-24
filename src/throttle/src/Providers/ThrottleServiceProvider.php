<?php

declare(strict_types=1);

namespace Larmias\Throttle\Providers;

use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ThrottleInterface;
use Larmias\Throttle\Throttle;

class ThrottleServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function register(): void
    {
        $this->container->bind(ThrottleInterface::class, Throttle::class);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}