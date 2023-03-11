<?php

declare(strict_types=1);

namespace Larmias\Pool;

use Larmias\Contracts\Pool\PoolOptionInterface;

class PoolOption implements PoolOptionInterface
{
    /**
     * @param int $minActive 最小活跃数
     * @param int $maxActive 最大活跃数
     * @param float $maxLifetime 最大可复用时间
     * @param float $maxIdleTime 最大空闲时间
     * @param float $waitTimeout 获取等待超时时间
     */
    public function __construct(
        protected int   $minActive = 1,
        protected int   $maxActive = 10,
        protected float $maxLifetime = -1,
        protected float $maxIdleTime = 60.0,
        protected float $waitTimeout = 3.0
    )
    {
    }

    /**
     * @return int
     */
    public function getMinActive(): int
    {
        return $this->minActive;
    }

    /**
     * @param int $minActive
     */
    public function setMinActive(int $minActive): void
    {
        $this->minActive = $minActive;
    }

    /**
     * @return int
     */
    public function getMaxActive(): int
    {
        return $this->maxActive;
    }

    /**
     * @param int $maxActive
     */
    public function setMaxActive(int $maxActive): void
    {
        $this->maxActive = $maxActive;
    }

    /**
     * @return float
     */
    public function getMaxLifetime(): float
    {
        return $this->maxLifetime;
    }

    /**
     * @param float $maxLifetime
     */
    public function setMaxLifetime(float $maxLifetime): void
    {
        $this->maxLifetime = $maxLifetime;
    }

    /**
     * @return float
     */
    public function getMaxIdleTime(): float
    {
        return $this->maxIdleTime;
    }

    /**
     * @param float $maxIdleTime
     */
    public function setMaxIdleTime(float $maxIdleTime): void
    {
        $this->maxIdleTime = $maxIdleTime;
    }

    /**
     * @return float
     */
    public function getWaitTimeout(): float
    {
        return $this->waitTimeout;
    }

    /**
     * @param float $waitTimeout
     */
    public function setWaitTimeout(float $waitTimeout): void
    {
        $this->waitTimeout = $waitTimeout;
    }
}