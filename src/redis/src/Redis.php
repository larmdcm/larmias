<?php

declare(strict_types=1);

namespace Larmias\Redis;

use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;

/**
 * @mixin ConnectionInterface
 */
class Redis
{
    /**
     * @var string|null
     */
    protected ?string $name = null;

    /**
     * @param RedisFactoryInterface $factory
     */
    public function __construct(protected RedisFactoryInterface $factory)
    {
    }

    /**
     * @return ConnectionInterface
     */
    public function getConnection(): ConnectionInterface
    {
        return $this->factory->get($this->name);
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->getConnection()->{$name}(...$arguments);
    }
}