<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Contracts;

interface MapInterface
{
    public function set(string $key, string $value): bool;

    public function get(string $key): mixed;

    public function del(string $key): bool;

    public function exists(string $key): bool;

    public function clear(): bool;

    public function count(): int;
}