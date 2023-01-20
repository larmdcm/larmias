<?php

declare(strict_types=1);

namespace Larmias\Redis\Providers;

use Larmias\Contracts\Redis\RedisFactoryInterface;
use Larmias\Framework\ServiceProvider;
use Larmias\Redis\RedisFactory;

class RedisServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(RedisFactoryInterface::class, RedisFactory::class);
    }
}