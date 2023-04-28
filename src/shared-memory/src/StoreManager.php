<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\SharedMemory\Contracts\ChannelInterface;
use Larmias\SharedMemory\Contracts\StrInterface;
use Larmias\SharedMemory\Store\Channel;
use Larmias\SharedMemory\Store\Str;

class StoreManager
{
    /**
     * @var array
     */
    protected static array $stores = [];

    /**
     * @var array|string[]
     */
    protected static array $container = [
        StrInterface::class => Str::class,
        ChannelInterface::class => Channel::class,
    ];

    /**
     * @param string $name
     * @param string $class
     * @return void
     */
    public static function addContainer(string $name, string $class): void
    {
        static::$container[$name] = $class;
    }

    /**
     * @return StrInterface
     */
    public static function map(): StrInterface
    {
        return static::getStore(__FUNCTION__, function () {
            return new static::$container[StrInterface::class];
        });
    }

    /**
     * @return ChannelInterface
     */
    public static function channel(): ChannelInterface
    {
        return static::getStore(__FUNCTION__, function () {
            return new static::$container[ChannelInterface::class];
        });
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return mixed
     */
    public static function getStore(string $name, callable $callback): mixed
    {
        $select = Context::getStoreSelect();
        if (!isset(static::$stores[$select])) {
            static::$stores[$select] = [];
        }
        if (!isset(static::$stores[$select][$name])) {
            static::$stores[$select][$name] = $callback();
        }
        return static::$stores[$select][$name];
    }
}