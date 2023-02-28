<?php

declare(strict_types=1);

namespace Larmias\Contracts;

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

    /**
     * @param string $process
     * @param string $name
     * @param int $count
     * @return void
     */
    public function addProcess(string $process, string $name, int $count = 1): void;

    /**
     * @param string|array $commands
     * @return void
     */
    public function commands(string|array $commands): void;
}