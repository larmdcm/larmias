<?php

declare(strict_types=1);

namespace Larmias\Contracts\Concurrent;

interface ParallelInterface
{
    public function add(callable $callable, $key = null): void;

    public function wait(bool $throw = true): array;

    public function count(): int;

    public function clear(): void;
}