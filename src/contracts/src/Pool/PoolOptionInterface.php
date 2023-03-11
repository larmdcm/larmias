<?php

declare(strict_types=1);

namespace Larmias\Pool\Contracts;

interface PoolOptionInterface
{
    /**
     * @return int
     */
    public function getMinActive(): int;

    /**
     * @param int $minActive
     */
    public function setMinActive(int $minActive): void;

    /**
     * @return int
     */
    public function getMaxActive(): int;

    /**
     * @param int $maxActive
     */
    public function setMaxActive(int $maxActive): void;

    /**
     * @return float
     */
    public function getMaxLifetime(): float;

    /**
     * @param float $maxLifetime
     */
    public function setMaxLifetime(float $maxLifetime): void;

    /**
     * @return float
     */
    public function getMaxIdleTime(): float;

    /**
     * @param float $maxIdleTime
     */
    public function setMaxIdleTime(float $maxIdleTime): void;

    /**
     * @return float
     */
    public function getConnectTimeout(): float;

    /**
     * @param float $connectTimeout
     */
    public function setConnectTimeout(float $connectTimeout): void;

    /**
     * @return float
     */
    public function getWaitTimeout(): float;

    /**
     * @param float $waitTimeout
     */
    public function setWaitTimeout(float $waitTimeout): void;
}