<?php

declare(strict_types=1);

namespace Larmias\Auth\Providers;

use Larmias\Auth\Facade\Auth;
use Larmias\Contracts\ContextInterface;
use Larmias\Framework\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        if ($this->container->has(ContextInterface::class)) {
            Auth::setContext($this->container->get(ContextInterface::class));
        }

        $this->publishes(static::class, [
            __DIR__ . '/../../publish/auth.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'auth.php',
        ]);
    }
}