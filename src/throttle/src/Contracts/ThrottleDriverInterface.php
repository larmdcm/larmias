<?php

declare(strict_types=1);

namespace Larmias\Throttle\Contracts;

interface ThrottleDriverInterface
{
    /**
     * @param string $key
     * @param float $microTime
     * @param int $maxRequests
     * @param int $duration
     * @return bool
     */
    public function allow(string $key, float $microTime, int $maxRequests, int $duration): bool;

    /**
     * @return int
     */
    public function getCurRequests(): int;

    /**
     * @return int
     */
    public function getWaitSeconds(): int;
}