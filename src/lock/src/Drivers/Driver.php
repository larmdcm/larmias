<?php

declare(strict_types=1);

namespace Larmias\Lock\Drivers;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LockerInterface;
use Larmias\Lock\Key;

abstract class Driver implements LockerInterface
{
    /**
     * @var array|string[]
     */
    protected array $config = [
        'wait_sleep_time' => 30,
        'wait_timeout' => 3000,
    ];

    /**
     * Driver constructor.
     * @param ContainerInterface $container
     * @param Key $key
     * @param array $config
     */
    public function __construct(protected ContainerInterface $container, protected Key $key, array $config = [])
    {
        $this->config = \array_merge($this->config, $config);

        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * 堵塞获取锁
     *
     * @param int|null $waitTimeout
     * @return bool
     */
    public function block(?int $waitTimeout = null): bool
    {
        $waitTimeout = $waitTimeout ?: $this->config['wait_timeout'];
        $beginTime = \microtime(true);
        $waitSleepTime = $this->config['wait_sleep_time'] * 1000;
        do {
            if ($this->acquire()) {
                return true;
            }
            if (0 === $waitTimeout || \microtime(true) - $beginTime < $waitTimeout / 1000) {
                \usleep($waitSleepTime);
            } else {
                break;
            }
        } while (true);

        return false;
    }
}