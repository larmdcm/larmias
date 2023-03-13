<?php

declare(strict_types=1);

namespace Larmias\Redis;

use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Redis\Pool\RedisConnection;
use Larmias\Redis\Pool\RedisPool;
use Larmias\Engine\Coroutine;
use function in_array;

class RedisProxy implements ConnectionInterface
{
    /**
     * @var RedisPool
     */
    protected RedisPool $redisPool;

    /**
     * @var ContextInterface
     */
    protected ContextInterface $context;

    /**
     * @param ContainerInterface $container
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, protected array $config = [])
    {
        $this->context = $this->container->get(ContextInterface::class);
        $this->redisPool = new RedisPool($this->container, $this->config['pool'] ?? [], $this->config);
    }

    /**
     * @param bool $hasContextConnection
     * @return RedisConnection
     */
    public function getConnection(bool $hasContextConnection = false): RedisConnection
    {
        if ($hasContextConnection) {
            return $this->context->get($this->getContextKey());
        }
        return $this->redisPool->get();
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments)
    {
        $contextKey = $this->getContextKey();
        $hasContextConnection = $this->context->has($contextKey);
        $connection = $this->getConnection($hasContextConnection);
        try {
            return $connection->{$name}(...$arguments);
        } finally {
            if (!$hasContextConnection) {
                if ($this->shouldUseSameConnection($name)) {
                    if ($name === 'select' && isset($arguments[0])) {
                        $connection->setDatabase((int)$arguments[0]);
                    }
                    $this->context->set($contextKey, $connection);
                    Coroutine::defer(function () use ($contextKey, $connection) {
                        $this->context->destroy($contextKey);
                        $connection->release();
                    });
                } else {
                    $connection->release();
                }
            }
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function shouldUseSameConnection(string $name): bool
    {
        return in_array($name, ['multi', 'pipeline', 'select']);
    }

    /**
     * @return string
     */
    protected function getContextKey(): string
    {
        return 'redis.connections.' . $this->config['name'];
    }

    /**
     * @return bool
     */
    public function reconnect(): bool
    {
        return $this->__call(__FUNCTION__, ...func_get_args());
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        return $this->__call(__FUNCTION__, ...func_get_args());
    }

    /**
     * @return \Redis
     */
    public function getRaw(): \Redis
    {
        return $this->__call(__FUNCTION__, ...func_get_args());
    }

    /**
     * @return int
     */
    public function getDatabase(): int
    {
        return $this->__call(__FUNCTION__, ...func_get_args());
    }

    /**
     * @param int $db
     * @return void
     */
    public function setDatabase(int $db): void
    {
        $this->__call(__FUNCTION__, ...func_get_args());
    }
}