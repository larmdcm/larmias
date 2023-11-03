<?php

declare(strict_types=1);

namespace Larmias\Command\Providers;

use Larmias\Framework\ServiceProvider;

class CommandServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/commands.php' => $this->app->getConfigPath() . 'commands.php',
        ]);
    }
}