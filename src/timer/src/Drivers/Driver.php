<?php

declare(strict_types=1);

namespace Larmias\Timer\Drivers;

use Larmias\Contracts\TimerInterface;

abstract class Driver implements TimerInterface
{
    /**
     * @var TimerInterface[]
     */
    protected static array $instances = [];

    /**
     * 获取单例对象
     *
     * @return TimerInterface
     */
    public static function getInstance(): TimerInterface
    {
        if (!isset(static::$instances[static::class])) {
            static::$instances[static::class] = new static();
        }
        return static::$instances[static::class];
    }
}