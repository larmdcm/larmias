<?php

declare(strict_types=1);

namespace Larmias\Http\CSRF\Providers;

use Larmias\Framework\ServiceProvider;
use Larmias\Http\CSRF\Contracts\CsrfManagerInterface;
use Larmias\Http\CSRF\CsrfManager;

class CsrfServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(CsrfManagerInterface::class, CsrfManager::class);
    }
}