<?php

declare(strict_types=1);

namespace Larmias\Contracts;

interface FileWatcherInterface
{
    /**
     * @var int
     */
    public const EVENT_ADD = 1;

    /**
     * @var int
     */
    public const EVENT_UPDATE = 2;

    /**
     * @var int
     */
    public const EVENT_DELETE = 3;

    /**
     * @param string|array $path
     * @return FileWatcherInterface
     */
    public function include(string|array $path): FileWatcherInterface;

    /**
     * @param string|array $path
     * @return FileWatcherInterface
     */
    public function exclude(string|array $path): FileWatcherInterface;

    /**
     * @param callable $callback
     * @return void
     */
    public function watch(callable $callback): void;
}