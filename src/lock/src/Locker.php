<?php

declare(strict_types=1);

namespace Larmias\Lock;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LockerInterface;
use Larmias\Lock\Driver\Redis;
use function array_merge;
use function is_string;

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
     */
    public function __construct(protected ContainerInterface $container, Key|string $key, ConfigInterface $config)
    {
        $this->config = array_merge($this->config, $config->get('lock', []));
        if (is_string($key)) {
            $key = new Key($key, $this->config['expire']);
        }
        $key->setPrefix($this->config['prefix']);
        /** @var LockerInterface $lock */
        $lock = $this->container->make($this->config['driver'], ['key' => $key, 'config' => $this->config], true);
        $this->lock = $lock;
    }

    /**
     * @return bool
     */
    public function acquire(): bool
    {
        return $this->lock->acquire();
    }

    /**
     * @param int|null $waitTimeout
     * @return bool
     */
    public function block(?int $waitTimeout = null): bool
    {
        return $this->lock->block($waitTimeout);
    }

    /**
     * @return bool
     */
    public function release(): bool
    {
        return $this->lock->release();
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