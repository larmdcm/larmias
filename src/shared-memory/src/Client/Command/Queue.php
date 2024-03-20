<?php

declare(strict_types=1);

namespace Larmias\SharedMemory\Client\Command;

use Larmias\SharedMemory\Client\Connection;

/**
 * @mixin Connection
 */
trait Queue
{
    /**
     * @param string $key
     * @param string $value
     * @return bool
     */
    public function enqueue(string $key, string $value): bool
    {
        $result = $this->command('queue:enqueue', [$key, $value]);
        return $result && $result->success;
    }

    /**
     * @param string $key
     * @return ?string
     */
    public function dequeue(string $key): ?string
    {
        $result = $this->command('queue:dequeue', [$key]);
        return $result && $result->success ? $result->data : null;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function qeIsEmpty(string $key): bool
    {
        $result = $this->command('queue:isEmpty', [$key]);
        return $result && $result->success ? $result->data : true;
    }

    /**
     * @param string $key
     * @return int
     */
    public function qeCount(string $key): int
    {
        $result = $this->command('queue:count', [$key]);
        return $result && $result->success ? $result->data : 0;
    }
}