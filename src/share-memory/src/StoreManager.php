<?php

declare(strict_types=1);

namespace Larmias\ShareMemory;

use Larmias\ShareMemory\Contracts\ChannelInterface;
use Larmias\ShareMemory\Contracts\MapInterface;
use Larmias\ShareMemory\Store\Channel;
use Larmias\ShareMemory\Store\Map;

class StoreManager
{
    protected static array $stores = [];

    protected static array $container = [
        MapInterface::class => Map::class,
        ChannelInterface::class => Channel::class,
    ];

    public static function setContainer(string $name, string $class): void
    {
        static::$container[$name] = $class;
    }

    public static function map(): MapInterface
    {
        return static::getStore(__FUNCTION__, function () {
            return new static::$container[MapInterface::class];
        });
    }

    public static function channel(): ChannelInterface
    {
        return static::getStore(__FUNCTION__, function () {
            return new static::$container[ChannelInterface::class];
        });
    }

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