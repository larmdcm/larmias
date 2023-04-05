<?php

declare(strict_types=1);

namespace Larmias\Cache\Providers;

use Larmias\Cache\Cache;
use Larmias\Contracts\CacheInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;

class CacheServiceProvider implements ServiceProviderInterface
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
            CacheInterface::class => Cache::class,
            PsrCacheInterface::class => CacheInterface::class,
        ]);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
    }
}