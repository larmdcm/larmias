<?php

declare(strict_types=1);

namespace Larmias\Cache;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\CacheInterface;
use Larmias\Contracts\ContainerInterface;
use function func_get_args;

class Cache implements CacheInterface
{
    /**
     * @var CacheInterface[]
     */
    protected array $stores = [];

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ConfigInterface $config)
    {
    }

    /**
     * 获取存储驱动
     *
     * @param string|null $name
     * @return CacheInterface
     */
    public function store(?string $name = null): CacheInterface
    {
        $name = $name ?: $this->getConfig('default');
        if (isset($this->stores[$name])) {
            return $this->stores[$name];
        }
        $storeConfig = $this->getConfig('stores.' . $name);
        /** @var CacheInterface $store */
        $store = $this->container->make($storeConfig['driver'], ['config' => $storeConfig]);
        return $this->stores[$name] = $store;
    }

    /**
     * 获取配置.
     *
     * @param string|null $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(?string $name = null, mixed $default = null): mixed
    {
        if ($name === null) {
            return $this->config->get('cache');
        }
        return $this->config->get('cache.' . $name, $default);
    }

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key, $default = null): mixed
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int|\DateTimeInterface|\DateInterval $ttl
     * @return bool
     */
    public function set($key, $value, $ttl = null): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return bool
     */
    public function clear(): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $keys
     * @param mixed $default
     * @return iterable
     */
    public function getMultiple($keys, $default = null): iterable
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $values
     * @param int|\DateTimeInterface|\DateInterval $ttl
     * @return bool
     */
    public function setMultiple($values, $ttl = null): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param array $keys
     * @return bool
     */
    public function deleteMultiple($keys): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has($key): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     * @param int $step
     * @return int|null
     */
    public function increment(string $key, int $step = 1): ?int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     * @param int $step
     * @return int|null
     */
    public function decrement(string $key, int $step = 1): ?int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param mixed $ttl
     * @return mixed
     */
    public function remember(string $key, mixed $value, mixed $ttl = null): mixed
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->store()->{$name}(...$arguments);
    }
}