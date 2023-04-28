<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

interface StrInterface
{
    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key): mixed;

    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function set(string $key, string $value): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function del(string $key): bool;

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool;

    /**
     * @param string $key
     * @param int $step
     * @return string
     */
    public function incr(string $key, int $step): string;

    /**
     * @param string $key
     * @param int $step
     * @return string
     */
    public function decr(string $key, int $step): string;

    /**
     * @return bool
     */
    public function clear(): bool;

    /**
     * @return int
     */
    public function count(): int;
}