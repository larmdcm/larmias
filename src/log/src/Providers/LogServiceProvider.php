<?php

declare(strict_types=1);

namespace Larmias\Log\Providers;

use Larmias\Contracts\Logger\LoggerFactoryInterface;
use Larmias\Contracts\LoggerInterface;
use Larmias\Log\LoggerFactory;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Larmias\Framework\ServiceProvider;
use Larmias\Log\Logger;

class LogServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf([
            LoggerInterface::class => Logger::class,
            PsrLoggerInterface::class => LoggerInterface::class,
            LoggerFactoryInterface::class => LoggerFactory::class,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/logger.php' => $this->app->getConfigPath() . 'logger.php',
        ]);
    }
}