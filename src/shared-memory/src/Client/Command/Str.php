<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Command;

use Larmias\SharedMemory\Client\Connection;

/**
 * @mixin Connection
 */
trait Str
{
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $result = $this->command('str:get', [$key]);
        return $result && $result->success ? $result->data : $default;
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set(string $key, string $value): bool
    {
        $result = $this->command('str:set', [$key, $value]);
        return $result && $result->success;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool
    {
        $result = $this->command('str:del', [$key]);
        return $result && $result->success;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        $result = $this->command('str:exists', [$key]);
        return $result && $result->success ? $result->data : false;
    }

    /**
     * @param string $key
     * @param int $step
     * @return int|false
     */
    public function incr(string $key, int $step = 1): int|false
    {
        $result = $this->command('str:incr', [$key, $step]);
        return $result && $result->success ? (int)$result->data : false;
    }

    /**
     * @param string $key
     * @param int $step
     * @return int|false
     */
    public function decr(string $key, int $step = 1): int|false
    {
        $result = $this->command('str:decr', [$key, $step]);
        return $result && $result->success ? (int)$result->data : false;
    }


    /**
     * @return bool
     */
    public function clear(): bool
    {
        $result = $this->command('str:clear');
        return $result && $result->success;
    }

    /**
     * @return int|null
     */
    public function count(): ?int
    {
        $result = $this->command('str:count');
        return $result && $result->success ? (int)$result->data : null;
    }
}