<?php

declare(strict_types=1);

namespace Larmias\Contracts\Redis;

interface RedisFactoryInterface
{
    /**
     * @param string $name
     * @return ConnectionInterface
     */
    public function get(string $name = 'default'): ConnectionInterface;
}