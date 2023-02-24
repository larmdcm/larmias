<?php

declare(strict_types=1);

namespace Larmias\Throttle\Drivers;

use Psr\SimpleCache\InvalidArgumentException;

/**
 * 计数器固定窗口算法
 */
class CounterFixed extends Driver
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
        $curRequests = (int)$this->cache->get($key, 0);
        $nowTime = (int)$microTime;
        $waitResetSeconds = $duration - $nowTime % $duration;
        $this->setWaitSeconds($waitResetSeconds % $duration + 1);
        $this->setCurRequests($curRequests);

        if ($curRequests < $maxRequests) {
            $this->cache->set($key, $curRequests, $waitResetSeconds);
            return true;
        }

        return false;
    }
}