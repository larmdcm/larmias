<?php

declare(strict_types=1);

namespace Larmias\Framework;

use Larmias\Contracts\ApplicationInterface;
use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ConsoleInterface;
use Larmias\Contracts\ServiceProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

abstract class ServiceProvider implements ServiceProviderInterface
{
    /**
     * @param ApplicationInterface $app
     */
    public function __construct(protected ApplicationInterface $app)
    {
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
    }

    /**
     * @return void
     */
    public function register(): void
    {
    }

    /**
     * @return void
     */
    public function boot(): void
    {
    }

    /**
     * @param string|array $commands
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function commands(string|array $commands): void
    {
        foreach ((array)$commands as $command) {
            /** @var ConsoleInterface $console */
            $console = $this->app->get(ConsoleInterface::class);
            $console->addCommand($command);
        }
    }
}