<?php

declare(strict_types=1);

namespace Larmias\Snowflake\Sequence;

use Godruoyi\Snowflake\SequenceResolver;
use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;

class RedisSequenceResolver implements SequenceResolver
{
    /**
     * @var ConnectionInterface
     */
    protected ConnectionInterface $connection;

    /**
     * @var string
     */
    protected string $prefix = '';

    /**
     * @param RedisFactoryInterface $factory
     */
    public function __construct(RedisFactoryInterface $factory)
    {
        $this->connection = $factory->get();
    }

    /**
     * @param int $currentTime
     * @return int
     */
    public function sequence(int $currentTime): int
    {
        $lua = "return redis.call('exists',KEYS[1])<1 and redis.call('psetex',KEYS[1],ARGV[2],ARGV[1])";

        $key = $this->prefix . $currentTime;
        if ($this->connection->eval($lua, [$key, 1, 1000], 1)) {
            return 0;
        }

        return $this->connection->incrby($key, 1);
    }

    /**
     * @return string
     */
    public function getPrefix(): string
    {
        return $this->prefix;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }
}