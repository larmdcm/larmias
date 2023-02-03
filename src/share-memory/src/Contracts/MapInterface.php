<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Contracts;

interface MapInterface
{
    public function set(string $key, string $value): bool;

    public function get(string $key): mixed;

    public function delete(string $key): bool;

    public function exists(string $key): bool;

    public function incr(string $key, int $step): string;

    public function decr(string $key, int $step): string;

    public function clear(): bool;

    public function count(): int;
}