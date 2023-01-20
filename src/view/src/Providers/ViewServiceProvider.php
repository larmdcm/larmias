<?php

declare(strict_types=1);

namespace Larmias\View\Providers;

use Larmias\Contracts\ViewInterface;
use Larmias\Framework\ServiceProvider;
use Larmias\View\View;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(ViewInterface::class, View::class);
    }
}