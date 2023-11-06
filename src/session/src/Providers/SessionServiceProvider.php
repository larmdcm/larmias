<?php

declare(strict_types=1);

namespace Larmias\Session\Providers;

use Larmias\Contracts\SessionInterface;
use Larmias\Session\SessionProxy;
use Larmias\Framework\ServiceProvider;

class SessionServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(SessionInterface::class, SessionProxy::class);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/session.php' => $this->app->getConfigPath() . 'session.php',
        ]);
    }
}