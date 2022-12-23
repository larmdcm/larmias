<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

interface WatcherInterface
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
     * @return WatcherInterface
     */
    public function include(string|array $path): WatcherInterface;

    /**
     * @param string|array $path
     * @return WatcherInterface
     */
    public function exclude(string|array $path): WatcherInterface;

    /**
     * @param string|array $ext
     * @return WatcherInterface
     */
    public function excludeExt(string|array $ext): WatcherInterface;

    /**
     * @param callable $callback
     * @return void
     */
    public function watch(callable $callback): void;
}