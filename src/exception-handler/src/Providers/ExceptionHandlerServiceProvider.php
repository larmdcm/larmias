<?php

declare(strict_types=1);

namespace Larmias\ExceptionHandler\Providers;

use Larmias\ExceptionHandler\Contracts\ExceptionHandlerDispatcherInterface;
use Larmias\ExceptionHandler\ExceptionHandlerDispatcher;
use Larmias\Framework\ServiceProvider;

class ExceptionHandlerServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(ExceptionHandlerDispatcherInterface::class, ExceptionHandlerDispatcher::class);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/exceptions.php' => $this->app->getConfigPath() . DIRECTORY_SEPARATOR . 'exceptions.php',
        ]);
    }
}