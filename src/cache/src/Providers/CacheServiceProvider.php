<?php

declare(strict_types=1);

namespace Larmias\Cache\Providers;

use Larmias\Cache\Cache;
use Larmias\Contracts\CacheInterface;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Larmias\Framework\ServiceProvider;

class CacheServiceProvider extends ServiceProvider
{
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
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/cache.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'cache.php',
        ]);
    }
}