<?php

declare(strict_types=1);

namespace Larmias\Lock;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LockerInterface;
use Larmias\Lock\Drivers\Redis;
use Larmias\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Closure;

class Locker implements LockerInterface
{
    /**
     * @var LockerInterface
     */
    protected LockerInterface $lock;

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

    /**
     * @param ContainerInterface $container
     * @param Key|string $key
     * @param Closure|null $callback
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(protected ContainerInterface $container, Key|string $key, protected ?Closure $callback = null)
    {
        /** @var ConfigInterface $config */
        $config = $this->container->get(ConfigInterface::class);
        $this->config = \array_merge($this->config, $config->get('lock', []));
        if (\is_string($key)) {
            $key = new Key($key, $this->config['expire']);
        }
        $key->setPrefix($this->config['prefix']);
        /** @var LockerInterface $lock */
        $lock = $this->container->make($this->config['driver'], ['key' => $key, 'config' => $config], true);
        $this->lock = $lock;
    }

    /**
     * @param Key|string $key
     * @param Closure|null $callback
     * @return LockerInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public static function create(Key|string $key, ?Closure $callback = null): LockerInterface
    {
        return new static(ApplicationContext::getContainer(), $key, $callback);
    }

    /**
     * @return bool
     */
    public function acquire(): bool
    {
        if (!$this->lock->acquire()) {
            return false;
        }
        return $this->resolve();
    }

    /**
     * @param int|null $waitTimeout
     * @return bool
     */
    public function block(?int $waitTimeout = null): bool
    {
        if (!$this->lock->block($waitTimeout)) {
            return false;
        }
        return $this->resolve();
    }

    /**
     * @return bool
     */
    public function release(): bool
    {
        return $this->__call(__FUNCTION__, func_get_args());
    }

    /**
     * @return bool
     */
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

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->lock->{$name}(...$arguments);
    }
}