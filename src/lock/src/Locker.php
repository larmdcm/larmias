<?php

declare(strict_types=1);

namespace Larmias\Lock;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LockInterface;
use Closure;
use Larmias\Lock\Drivers\Redis;
use Larmias\Utils\ApplicationContext;

class Locker implements LockInterface
{
    /**
     * @var LockInterface
     */
    protected LockInterface $lock;

    /**
     * @var array
     */
    protected array $config = [
        'driver' => Redis::class,
        'prefix' => '',
        'expire' => 30000,
        'wait_sleep_time' => 30,
        'wait_timeout' => 10000,
    ];

    public function __construct(protected ContainerInterface $container, Key|string $key, protected ?Closure $callback = null, array $config = [])
    {
        $this->config = \array_merge($this->config, $config);
        if (\is_string($key)) {
            $key = new Key($key, $this->config['expire']);
        }
        $key->setPrefix($this->config['prefix']);
        /** @var LockInterface $lock */
        $lock = $this->container->make($this->config['driver'], ['key' => $key, 'config' => $config], true);
        $this->lock = $lock;
    }

    public static function create(Key|string $key, ?Closure $callback = null): LockInterface
    {
        $container = ApplicationContext::getContainer();
        /** @var ConfigInterface $config */
        $config = $container->get(ConfigInterface::class);
        return new static($container, $key, $callback, $config->get('lock', []));
    }

    public function acquire(): bool
    {
        if (!$this->lock->acquire()) {
            return false;
        }
        return $this->resolve();
    }

    public function block(?int $waitTimeout = null): bool
    {
        if (!$this->lock->block($waitTimeout)) {
            return false;
        }
        return $this->resolve();
    }

    public function release(): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    protected function resolve(): bool
    {
        $result = true;
        try {
            if ($this->callback !== null) {
                $result = \call_user_func($this->callback);
                $this->release();
            }
        } catch (\Throwable $e) {
            $this->release();
        }
        return \is_bool($result) ? $result : true;
    }

    public function __call(string $name, array $arguments): mixed
    {
        return $this->lock->{$name}(...$arguments);
    }
}