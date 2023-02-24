<?php

declare(strict_types=1);

namespace Larmias\Framework\Contracts;

interface ServiceDiscoverInterface
{
    /**
     * @var string
     */
    public const SERVICE_PROCESS = 'process';

    /**
     * @var string
     */
    public const SERVICE_COMMAND = 'commands';

    /**
     * @var string
     */
    public const SERVICE_PROVIDER = 'providers';

    /**
     * @param \Closure $callback
     * @return void
     */
    public function discover(\Closure $callback): void;

    /**
     * @param string $name
     * @param string $class
     * @param array $args
     * @return void
     */
    public function register(string $name, string $class, array $args = []): void;

    /**
     * @return array
     */
    public function services(): array;
}