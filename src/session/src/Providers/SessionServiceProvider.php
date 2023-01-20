<?php

declare(strict_types=1);

namespace Larmias\Session\Providers;

use Larmias\Contracts\SessionInterface;
use Larmias\Framework\ServiceProvider;
use Larmias\Session\Session;

class SessionServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SessionInterface::class, Session::class);
    }
}