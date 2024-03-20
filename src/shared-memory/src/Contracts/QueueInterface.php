<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Contracts;

interface QueueInterface
{
    /**
     * @param string $key
     * @param string $data
     * @return bool
     */
    public function enqueue(string $key, string $data): bool;

    /**
     * @param string $key
     * @return string|null
     */
    public function dequeue(string $key): ?string;

    /**
     * @param string $key
     * @return bool
     */
    public function isEmpty(string $key): bool;

    /**
     * @param string $key
     * @return int
     */
    public function count(string $key): int;
}