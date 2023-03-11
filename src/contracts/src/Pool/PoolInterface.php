<?php

declare(strict_types=1);

namespace Larmias\Contracts\Pool;

interface PoolInterface
{
    /**
     * @return ConnectionInterface
     */
    public function createConnection(): ConnectionInterface;

    /**
     * @return ConnectionInterface
     */
    public function get(): ConnectionInterface;

    /**
     * @param ConnectionInterface $connection
     * @return bool
     */
    public function release(ConnectionInterface $connection): bool;

    /**
     * @return bool
     */
    public function close(): bool;
}