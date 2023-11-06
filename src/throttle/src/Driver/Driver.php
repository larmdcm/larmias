<?php

declare(strict_types=1);

namespace Larmias\Throttle\Driver;

use Larmias\Contracts\ContextInterface;
use Larmias\Throttle\Contracts\ThrottleDriverInterface;
use Psr\SimpleCache\CacheInterface;

abstract class Driver implements ThrottleDriverInterface
{
    /**
     * @param CacheInterface $cache
     * @param ContextInterface $context
     */
    public function __construct(protected CacheInterface $cache, protected ContextInterface $context)
    {
    }

    public function getCurRequests(): int
    {
        return $this->context->get(__CLASS__ . '.cur_requests', 0);
    }

    public function setCurRequests(int $curRequests): int
    {
        $this->context->set(__CLASS__ . '.cur_requests', $curRequests);
        return $curRequests;
    }

    public function getWaitSeconds(): int
    {
        return $this->context->get(__CLASS__ . '.wait_seconds', 0);
    }

    public function setWaitSeconds(int $waitSeconds): int
    {
        $this->context->get(__CLASS__ . '.wait_seconds', $waitSeconds);
        return $waitSeconds;
    }
}