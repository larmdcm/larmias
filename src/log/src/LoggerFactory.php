<?php

declare(strict_types=1);

namespace Larmias\Log;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\Logger\LoggerFactoryInterface;
use Larmias\Contracts\LoggerInterface;

class LoggerFactory implements LoggerFactoryInterface
{
    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    /**
     * @param string|null $name
     * @return LoggerInterface
     */
    public function make(?string $name = null): LoggerInterface
    {
        /** @var Logger $logger */
        $logger = $this->container->make(Logger::class);
        return $logger->channel($name);
    }
}