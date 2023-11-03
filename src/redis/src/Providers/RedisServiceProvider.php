<?php

declare(strict_types=1);

namespace Larmias\Redis\Providers;

use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\Framework\ServiceProvider;
use Larmias\Redis\RedisFactory;

class RedisServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(RedisFactoryInterface::class, RedisFactory::class);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/redis.php' => $this->app->getConfigPath() . 'redis.php',
        ]);
    }
}