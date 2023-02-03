<?php

declare(strict_types=1);

namespace Larmias\Snowflake\Providers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Larmias\Snowflake\Contracts\IdGeneratorInterface;
use Larmias\Snowflake\IdGenerator;

class SnowflakeServiceProvider implements ServiceProviderInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bind(IdGeneratorInterface::class, IdGenerator::class);
    }

    /**
     * @return void
     */
    public function boot(): void
    {
    }
}