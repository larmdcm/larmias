<?php

declare(strict_types=1);

namespace Larmias\Redis\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Redis\RedisFactory;

class RedisServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {

    }

    public function register(): void
    {
        $this->container->bind(RedisFactoryInterface::class, RedisFactory::class);
    }

    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}