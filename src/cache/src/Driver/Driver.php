<?php

declare(strict_types=1);

namespace Larmias\Cache\Driver;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\PackerInterface;
use Larmias\Contracts\CacheInterface;
use DateTimeInterface;
use DateInterval;
use DateTime;
use InvalidArgumentException;
use Closure;

abstract class Driver implements CacheInterface
{
    protected array $config = [
        'prefix' => ''
    ];

    protected PackerInterface $packer;

    protected object $handler;

    public function __construct(protected ContainerInterface $container, array $config = [])
    {
        $this->config = \array_merge($this->config, $config);
        /** @var PackerInterface $packer */
        $packer = $this->container->make($this->config['packer']);
        $this->packer = $packer;

        if (method_exists($this,'initialize')) {
            $this->container->invoke([$this,'initialize']);
        }
    }

    public function getMultiple($keys, $default = null): iterable
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException('The keys is invalid!');
        }

        $result = [];
        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $default);
        }
        return $result;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        if (!is_array($values)) {
            throw new InvalidArgumentException('The values is invalid!');
        }

        foreach ($values as $key => $val) {
            $this->set($key, $val, $ttl);
        }

        return true;
    }

    public function deleteMultiple($keys): bool
    {
        if (!is_array($keys)) {
            throw new InvalidArgumentException('The keys is invalid!');
        }

        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    /**
     * 不存在写缓存
     *
     * @param string $key
     * @param mixed $value
     * @param null $ttl
     * @return mixed
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Throwable
     */
    public function remember(string $key, mixed $value, $ttl = null): mixed
    {
        if (($result = $this->get($key)) !== null) {
            return $result;
        }

        if (\is_null($ttl)) {
            $ttl = $this->config['expire'];
        }

        $time = \time();
        $lockKey = $key . '_lock';

        while ($time + 5 > \time() && $this->has($lockKey)) {
            // 存在锁定则等待
            \usleep(200000);
        }

        try {
            // 锁定
            $this->set($lockKey, true);

            if ($value instanceof Closure) {
                // 获取缓存数据
                $value = $this->container->invoke($value);
            }

            // 缓存数据
            $this->set($key, $value, $this->getExpireTime($ttl));

            // 解锁
            $this->delete($lockKey);
        } catch (\Throwable $e) {
            $this->delete($lockKey);
            throw $e;
        }

        return $value;
    }

    public function getHandler(): object
    {
        return $this->handler;
    }

    /**
     * 获取有效期
     *
     * @param int|DateTimeInterface|DateInterval $expire
     * @return int
     */
    protected function getExpireTime(int|DateTimeInterface|DateInterval $expire): int
    {
        if ($expire instanceof DateTimeInterface) {
            $expire = $expire->getTimestamp() - \time();
        } elseif ($expire instanceof DateInterval) {
            $expire = DateTime::createFromFormat('U', (string)\time())
                    ->add($expire)
                    ->format('U') - \time();
        }

        return (int)$expire;
    }

    protected function getCacheKey(string $key): string
    {
        return $this->config['prefix'] . $key;
    }
}