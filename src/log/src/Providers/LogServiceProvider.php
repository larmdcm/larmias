<?php

declare(strict_types=1);

namespace Larmias\Log\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LoggerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Log\Logger;

class LogServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ContainerInterface $container
     */
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf([
            LoggerInterface::class => Logger::class,
            PsrLoggerInterface::class => LoggerInterface::class,
        ]);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
    }
}