<?php

declare(strict_types=1);

namespace Larmias\Throttle;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\ThrottleInterface;
use Larmias\Throttle\Contracts\ThrottleDriverInterface;
use Larmias\Throttle\Drivers\CounterSlider;
use function array_merge;
use function microtime;

class Throttle implements ThrottleInterface
{
    /**
     * @var array
     */
    protected array $config = [
        // 限流算法驱动
        'driver' => CounterSlider::class,
        // 键前缀
        'prefix' => 'throttle:',
    ];

    /**
     * @var ThrottleDriverInterface
     */
    protected ThrottleDriverInterface $driver;

    /**
     * @param ContainerInterface $container
     * @param ConfigInterface $config
     */
    public function __construct(protected ContainerInterface $container, protected ContextInterface $context, ConfigInterface $config)
    {
        $this->config = array_merge($this->config, $config->get('throttle', []));
        /** @var ThrottleDriverInterface $driver */
        $driver = $this->container->make($this->config['driver']);
        $this->driver = $driver;
    }

    /**
     * @param string $key
     * @param array $rateLimit
     * @return bool
     */
    public function allow(string $key, array $rateLimit = []): bool
    {
        if (!$key || !$rateLimit) {
            return true;
        }
        $key = $this->getKey($key);
        $microTime = microtime(true);
        [$maxRequests, $duration] = $rateLimit;
        $allow = $this->driver->allow($key, $microTime, $maxRequests, $duration);
        $this->context->set(__CLASS__ . '.allow', [
            'now_time' => (int)$microTime,
            'expire' => $duration,
            'max_requests' => $maxRequests,
            'remaining' => $maxRequests - $this->driver->getCurRequests(),
            'wait_seconds' => $this->driver->getWaitSeconds(),
        ]);
        return $allow;
    }

    /**
     * @return array
     */
    public function getAllowInfo(): array
    {
        return $this->context->get(__CLASS__ . '.allow', []);
    }

    /**
     * @param string $key
     * @return string
     */
    protected function getKey(string $key): string
    {
        return $this->config['prefix'] . $key;
    }
}