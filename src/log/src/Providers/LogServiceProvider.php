<?php

declare(strict_types=1);

namespace Larmias\Log\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LoggerInterface;
use Psr\Log\LoggerInterface as PsrLoggerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Log\Logger;
use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\VendorPublishInterface;

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
        if ($this->container->has(ApplicationInterface::class) && $this->container->has(VendorPublishInterface::class)) {
            $app = $this->container->get(ApplicationInterface::class);
            $this->container->get(VendorPublishInterface::class)->publishes(static::class, [
                __DIR__ . '/../../publish/logger.php' => $app->getConfigPath() . 'logger.php',
            ]);
        }
    }
}