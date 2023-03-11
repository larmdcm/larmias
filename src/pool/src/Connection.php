<?php

declare(strict_types=1);

namespace Larmias\Pool;

use Larmias\Contracts\Pool\ConnectionInterface;
use Larmias\Contracts\Pool\PoolInterface;

abstract class Connection implements ConnectionInterface
{
    /**
     * @var int
     */
    protected int $connectTime = 0;

    /**
     * @var int
     */
    protected int $lastActiveTime = 0;

    /**
     * @var PoolInterface
     */
    protected PoolInterface $pool;

    /**
     * @return bool
     */
    public function release(): bool
    {
        return $this->pool->release($this);
    }

    /**
     * @return PoolInterface
     */
    public function getPool(): PoolInterface
    {
        return $this->pool;
    }

    /**
     * @param PoolInterface $pool
     */
    public function setPool(PoolInterface $pool): void
    {
        $this->pool = $pool;
    }

    /**
     * @return int
     */
    public function getConnectTime(): int
    {
        return $this->connectTime;
    }

    /**
     * @param int $connectTime
     */
    public function setConnectTime(int $connectTime): void
    {
        $this->connectTime = $connectTime;
    }

    /**
     * @return int
     */
    public function getLastActiveTime(): int
    {
        return $this->lastActiveTime;
    }

    /**
     * @param int $lastActiveTime
     */
    public function setLastActiveTime(int $lastActiveTime): void
    {
        $this->lastActiveTime = $lastActiveTime;
    }
}