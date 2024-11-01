<?php

declare(strict_types=1);

namespace Larmias\Event\Providers;

use Larmias\Framework\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/listeners.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'listeners.php',
        ]);
    }
}