<?php

declare(strict_types=1);

namespace Larmias\Contracts\Redis;

interface RedisFactoryInterface
{
    public function get(string $name = 'default'): ConnectionInterface;
}