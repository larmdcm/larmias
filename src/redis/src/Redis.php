<?php

declare(strict_types=1);

namespace Larmias\Redis;

use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;

/**
 * @mixin Connection
 */
class Redis
{
    protected string $name = 'default';

    public function __construct(protected RedisFactoryInterface $factory)
    {
    }

    public function getConnection(): ConnectionInterface
    {
        return $this->factory->get($this->name);
    }

    public function __call(string $name, array $arguments)
    {
        try {
            $result = $this->getConnection()->{$name}(...$arguments);
        } catch (\Throwable $e) {
            throw $e;
        }
        return $result;
    }
}