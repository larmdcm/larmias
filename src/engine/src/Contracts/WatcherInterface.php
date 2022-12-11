<?php

namespace Larmias\Engine\Contracts;

interface WatcherInterface
{
    /**
     * @param string|array $path
     * @return WatcherInterface
     */
    public function add(string|array $path): WatcherInterface;

    /**
     * @param callable $callback
     * @return void
     */
    public function watch(callable $callback): void;
}