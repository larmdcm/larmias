<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Command;

class Str extends Command
{
    /**
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $result = $this->client->command('str:get', [$key]);
        return $result && $result->success ? $result->data : $default;
    }

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set(string $key, string $value): bool
    {
        $result = $this->client->command('str:set', [$key, $value]);
        return $result && $result->success;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool
    {
        $result = $this->client->command('str:del', [$key]);
        return $result && $result->success;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        $result = $this->client->command('str:exists', [$key]);
        return $result && $result->success ? $result->data : false;
    }

    /**
     * @param string $key
     * @param int $step
     * @return string|false
     */
    public function incr(string $key, int $step = 1): string|false
    {
        $result = $this->client->command('str:incr', [$key, $step]);
        return $result && $result->success ? $result->data : false;
    }

    /**
     * @param string $key
     * @param int $step
     * @return string|false
     */
    public function decr(string $key, int $step = 1): string|false
    {
        $result = $this->client->command('str:decr', [$key, $step]);
        return $result && $result->success ? $result->data : false;
    }


    /**
     * @return bool
     */
    public function clear(): bool
    {
        $result = $this->client->command('str:clear');
        return $result && $result->success;
    }

    /**
     * @return int|null
     */
    public function count(): ?int
    {
        $result = $this->client->command('str:count');
        return $result && $result->success ? (int)$result->data : null;
    }
}