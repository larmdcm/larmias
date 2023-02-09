<?php

declare(strict_types=1);

namespace Larmias\Cache\Driver;

use Larmias\Contracts\Redis\ConnectionInterface;
use Larmias\Contracts\Redis\RedisFactoryInterface;

/**
 * @property ConnectionInterface $handler
 */
class Redis extends Driver
{
    /**
     * @var object
     */
    protected object $handler;

    /**
     * @var array
     */
    protected array $config = [
        'prefix' => '',
        'expire' => 0,
        'handler' => null,
    ];

    public function initialize(): void
    {
        if (\is_callable($this->config['handler'])) {
            $this->handler = $this->container->invoke($this->config['handler']);
        } else {
            /** @var RedisFactoryInterface $factory */
            $factory = $this->container->get(RedisFactoryInterface::class);
            $this->handler = $factory->get('cache');
        }
    }

    public function get($key, $default = null): mixed
    {
        $value = $this->handler->get($key);
        if ($value === false || \is_null($value)) {
            return $default;
        }
        return $this->packer->unpack($value);
    }

    public function set($key, $value, $ttl = null): bool
    {
        if (\is_null($ttl)) {
            $ttl = $this->config['expire'];
        }
        $key = $this->getCacheKey($key);
        $expire = $this->getExpireTime($ttl);
        $value = $this->packer->pack($value);

        if ($expire) {
            $result = $this->handler->setex($key, $expire, $value);
        } else {
            $result = $this->handler->set($key, $value);
        }

        return (bool)$result;
    }


    public function delete($key): bool
    {
        return $this->handler->del($this->getCacheKey($key)) > 0;
    }

    public function clear(): bool
    {
        return $this->handler->flushDB();
    }

    public function has($key): bool
    {
        return (bool)$this->handler->exists($this->getCacheKey($key));
    }

    public function increment(string $key, int $step = 1): ?int
    {
        return $this->handler->incrBy($this->getCacheKey($key), $step);
    }

    public function decrement(string $key, int $step = 1): ?int
    {
        return $this->handler->decrBy($this->getCacheKey($key), $step);
    }
}