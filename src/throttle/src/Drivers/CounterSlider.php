<?php

declare(strict_types=1);

namespace Larmias\Throttle\Drivers;

use Psr\SimpleCache\InvalidArgumentException;
use function array_values;
use function array_filter;
use function count;
use function max;

/**
 * 计数器滑动窗口算法
 */
class CounterSlider extends Driver
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
        $history = $this->cache->get($key, []);
        $nowTime = (int)$microTime;
        // 移除过期的请求的记录
        $history = array_values(array_filter($history, fn($val) => $val >= $nowTime - $duration));

        $curRequests = $this->setCurRequests(count($history));
        if ($curRequests < $maxRequests) {
            // 允许访问
            $history[] = $nowTime;
            $this->cache->set($key, $history, $duration);
            return true;
        }

        if ($history) {
            $waitSeconds = $duration - ($nowTime - $history[0]) + 1;
            $this->setWaitSeconds(max($waitSeconds, 0));
        }

        return false;
    }
}