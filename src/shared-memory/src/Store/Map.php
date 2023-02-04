<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Store;

use Larmias\SharedMemory\Contracts\MapInterface;

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

    public function incr(string $key, int $step): string
    {
        if (!$this->exists($key)) {
            $this->set($key, (string)$step);
            return (string)$step;
        }
        $value = (int)$this->get($key);
        $value += $step;
        return (string)$value;
    }

    public function decr(string $key, int $step): string
    {
        return $this->incr($key, -$step);
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