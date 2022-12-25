<?php

declare(strict_types=1);

namespace Larmias\ShareMemory\Store;

use Larmias\ShareMemory\Contracts\MapInterface;

class Map implements MapInterface
{
    protected array $data = [];

    public function set(string $key, string $value): bool
    {
        $this->data[$key] = $value;
        return true;
    }

    public function get(string $key): mixed
    {
        return $this->data[$key] ?? null;
    }

    public function delete(string $key): bool
    {
        unset($this->data[$key]);
        return true;
    }

    public function exists(string $key): bool
    {
        return \array_key_exists($key, $this->data);
    }

    public function clear(): bool
    {
        $this->data = [];
        return true;
    }

    public function count(): int
    {
        return \count($this->data);
    }
}