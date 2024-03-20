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
    public function strGet(string $key, mixed $default = null): mixed
    {
        $result = $this->command('str:get', [$key]);
        return $result && $result->success ? $result->data : $default;
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function strSet(string $key, string $value): bool
    {
        $result = $this->command('str:set', [$key, $value]);
        return $result && $result->success;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function strDel(string $key): bool
    {
        $result = $this->command('str:del', [$key]);
        return $result && $result->success;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function strExists(string $key): bool
    {
        $result = $this->command('str:exists', [$key]);
        return $result && $result->success ? $result->data : false;
    }

    /**
     * @param string $key
     * @param int $step
     * @return string|false
     */
    public function strIncr(string $key, int $step = 1): string|false
    {
        $result = $this->command('str:incr', [$key, $step]);
        return $result && $result->success ? $result->data : false;
    }

    /**
     * @param string $key
     * @param int $step
     * @return string|false
     */
    public function strDecr(string $key, int $step = 1): string|false
    {
        $result = $this->command('str:decr', [$key, $step]);
        return $result && $result->success ? $result->data : false;
    }


    /**
     * @return bool
     */
    public function strClear(): bool
    {
        $result = $this->command('str:clear');
        return $result && $result->success;
    }

    /**
     * @return int|null
     */
    public function strCount(): ?int
    {
        $result = $this->command('str:count');
        return $result && $result->success ? (int)$result->data : null;
    }
}