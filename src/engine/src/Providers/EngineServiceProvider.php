<?php

declare(strict_types=1);

namespace Larmias\Engine\Providers;

use Larmias\Framework\ServiceProvider;

class EngineServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/engine.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'engine.php',
        ]);
    }
}