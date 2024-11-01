<?php

declare(strict_types=1);

namespace Larmias\Throttle\Providers;

use Larmias\Contracts\ThrottleInterface;
use Larmias\Throttle\Throttle;
use Larmias\Framework\ServiceProvider;

class ThrottleServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(ThrottleInterface::class, Throttle::class);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/throttle.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'throttle.php',
        ]);
    }
}