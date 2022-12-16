<?php

declare(strict_types=1);

namespace Larmias\Engine\Contracts;

interface WatcherInterface
{
    /**
     * @param string|array $path
     * @return WatcherInterface
     */
    public function include(string|array $path): WatcherInterface;

    /**
     * @param callable $callback
     * @return void
     */
    public function watch(callable $callback): void;
}