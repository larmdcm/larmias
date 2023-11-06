<?php

declare(strict_types=1);

namespace Larmias\Throttle\Driver;

use Psr\SimpleCache\InvalidArgumentException;
use function floor;
use function min;
use function ceil;

class TokenBucket extends Driver
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
        if ($maxRequests <= 0 || $duration <= 0) return false;

        $assistKey = $key . ':store_num';
        $rate = (float)$maxRequests / $duration;

        $lastTime = $this->cache->get($key, null);
        $storeNum = $this->cache->get($assistKey, null);

        if ($lastTime === null || $storeNum === null) {
            $this->cache->set($key, $microTime, $duration);
            $this->cache->set($assistKey, $maxRequests - 1, $duration);
            return true;
        }

        $create_num = floor(($microTime - $lastTime) * $rate);
        $token_left = (int)min($maxRequests, $storeNum + $create_num);

        if ($token_left < 1) {
            $tmp = (int)ceil($duration / $maxRequests);
            $this->setWaitSeconds($tmp - ($microTime - $lastTime) % $tmp);
            return false;
        }
        $this->setCurRequests($maxRequests - $token_left);
        $this->cache->set($key, $microTime, $duration);
        $this->cache->set($assistKey, $token_left - 1, $duration);
        return true;
    }
}