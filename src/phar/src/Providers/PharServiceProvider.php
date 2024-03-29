<?php

declare(strict_types=1);

namespace Larmias\Phar\Providers;

use Larmias\Framework\ServiceProvider;
use Larmias\Phar\Commands\BuildCommand;

class PharServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->commands([
            BuildCommand::class
        ]);
    }
}