<?php

declare(strict_types=1);

namespace Larmias\Contracts\Pool;

interface ConnectionInterface
{
    /**
     * @return bool
     */
    public function connect(): bool;

    /**
     * @return bool
     */
    public function reset(): bool;

    /**
     * @return bool
     */
    public function ping(): bool;

    /**
     * @return bool
     */
    public function isConnected(): bool;

    /**
     * @return bool
     */
    public function close(): bool;

    /**
     * @return bool
     */
    public function release(): bool;

    /**
     * @return PoolInterface
     */
    public function getPool(): PoolInterface;

    /**
     * @param PoolInterface $pool
     * @return void
     */
    public function setPool(PoolInterface $pool): void;

    /**
     * @return int
     */
    public function getConnectTime(): int;

    /**
     * @param int $connectTime
     * @return void
     */
    public function setConnectTime(int $connectTime): void;

    /**
     * @return int
     */
    public function getLastActiveTime(): int;

    /**
     * @param int $lastActiveTime
     * @return void
     */
    public function setLastActiveTime(int $lastActiveTime): void;
}