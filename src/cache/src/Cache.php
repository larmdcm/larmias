<?php

declare(strict_types=1);

namespace Larmias\Cache;

use Larmias\Contracts\ConfigInterface;

use Larmias\Contracts\CacheInterface;
use Larmias\Contracts\ContainerInterface;

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
        if (\is_null($name)) {
            return $this->config->get('cache');
        }
        return $this->config->get('cache.' . $name, $default);
    }

    public function get($key, $default = null): mixed
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function set($key, $value, $ttl = null): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function delete($key): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function clear(): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function setMultiple($values, $ttl = null): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function deleteMultiple($keys): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function has($key): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function increment(string $key, int $step = 1): ?int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function decrement(string $key, int $step = 1): ?int
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function remember(string $key, mixed $value, $ttl = null): mixed
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->store()->{$name}(...$arguments);
    }
}