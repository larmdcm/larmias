<?php

declare(strict_types=1);

namespace Larmias\Contracts\Redis;

interface RedisFactoryInterface
{
    /**
     * @param string|null $name
     * @return ConnectionInterface
     */
    public function get(?string $name = null): ConnectionInterface;
}