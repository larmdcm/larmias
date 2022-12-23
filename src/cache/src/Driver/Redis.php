<?php

declare(strict_types=1);

namespace Larmias\Cache\Driver;

class Redis extends Driver
{
    /**
     * @var \Redis|\Predis\Client
     */
    protected object $handler;

    protected array $config = [
        'host' => '127.0.0.1',
        'port' => 6379,
        'password' => '',
        'select' => 0,
        'timeout' => 0,
        'expire' => 0,
        'persistent' => false,
        'prefix' => '',
        'handler' => null
    ];

    public function initialize(): void
    {
        if (\is_callable($this->config['handler'])) {
            $this->handler = $this->container->invoke($this->config['handler']);
        } else {
            $this->handler = static::makeRedis($this->config);
        }
    }

    public static function makeRedis(array &$config = []): object
    {
        if (extension_loaded('redis')) {
            $handler = new \Redis;

            if ($config['persistent']) {
                $handler->pconnect($config['host'], (int)$config['port'], (int)$config['timeout'], 'persistent_id_' . $config['select']);
            } else {
                $handler->connect($config['host'], (int)$config['port'], (int)$config['timeout']);
            }

            if ('' != $config['password']) {
                $handler->auth($config['password']);
            }
        } elseif (class_exists('\Predis\Client')) {
            $params = [];
            foreach ($config as $key => $val) {
                if (in_array($key, ['aggregate', 'cluster', 'connections', 'exceptions', 'prefix', 'profile', 'replication', 'parameters'])) {
                    $params[$key] = $val;
                    unset($config[$key]);
                }
            }

            if ('' == $config['password']) {
                unset($config['password']);
            }

            $handler = new \Predis\Client($config, $params);

            $config['prefix'] = '';
        } else {
            throw new \BadFunctionCallException('not support: redis');
        }
        if (0 != $config['select']) {
            $handler->select((int)$config['select']);
        }

        return $handler;
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
        $key    = $this->getCacheKey($key);
        $expire = $this->getExpireTime($ttl);
        $value  = $this->packer->pack($value);

        if ($expire) {
            $this->handler->setex($key, $expire, $value);
        } else {
            $this->handler->set($key, $value);
        }

        return true;
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
        return $this->handler->incrBy($this->getCacheKey($key),$step);
    }

    public function decrement(string $key, int $step = 1): ?int
    {
        return $this->handler->decrBy($this->getCacheKey($key),$step);
    }
}