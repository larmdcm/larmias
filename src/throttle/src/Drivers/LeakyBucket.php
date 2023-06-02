<?php

declare(strict_types=1);

namespace Larmias\Throttle\Drivers;

use Psr\SimpleCache\InvalidArgumentException;
use function ceil;

/**
 * 漏桶算法
 */
class LeakyBucket extends Driver
{
    /**
     * @param string $key
     * @param float $microTime
     * @param int $maxRequests
     * @param int $duration
     * @return bool
     * @throws InvalidArgumentException
     */
    public function allow(string $key, float $microTime, int $maxRequests, int $duration): bool
    {
        if ($maxRequests <= 0) return false;

        $lastTime = (float)$this->cache->get($key, 0);
        $rate = (float)$duration / $maxRequests;
        if ($microTime - $lastTime < $rate) {
            $this->setCurRequests(1);
            $this->setWaitSeconds((int)ceil($rate - ($microTime - $lastTime)));
            return false;
        }

        $this->cache->set($key, $microTime, $duration);
        return true;
    }
}