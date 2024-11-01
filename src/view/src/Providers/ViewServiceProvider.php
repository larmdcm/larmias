<?php

declare(strict_types=1);

namespace Larmias\View\Providers;

use Larmias\Contracts\ViewInterface;
use Larmias\View\View;
use Larmias\Framework\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(ViewInterface::class, View::class);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/view.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'view.php',
        ]);
    }
}