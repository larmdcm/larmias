<?php

declare(strict_types=1);

namespace Larmias\Snowflake\Providers;

use Larmias\Snowflake\Contracts\IdGeneratorInterface;
use Larmias\Snowflake\IdGenerator;
use Larmias\Framework\ServiceProvider;

class SnowflakeServiceProvider extends ServiceProvider
{
    /**
     * @return void
     */
    public function register(): void
    {
        $this->container->bindIf(IdGeneratorInterface::class, IdGenerator::class);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function boot(): void
    {
        $this->publishes(static::class, [
            __DIR__ . '/../../publish/snowflake.php' => $this->app->getConfigPath() . 'snowflake.php',
        ]);
    }
}