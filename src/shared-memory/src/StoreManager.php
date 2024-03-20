<?php

declare(strict_types=1);

namespace Larmias\SharedMemory;

use Larmias\SharedMemory\Contracts\ChannelInterface;
use Larmias\SharedMemory\Contracts\QueueInterface;
use Larmias\SharedMemory\Contracts\StrInterface;
use Larmias\SharedMemory\Store\Channel;
use Larmias\SharedMemory\Store\Queue;
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
        QueueInterface::class => Queue::class,
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
     * @throws \Throwable
     */
    public static function str(): StrInterface
    {
        return static::getStore(__FUNCTION__, function () {
            return new static::$container[StrInterface::class];
        });
    }

    /**
     * @return ChannelInterface
     * @throws \Throwable
     */
    public static function channel(): ChannelInterface
    {
        return static::getStore(__FUNCTION__, function () {
            return new static::$container[ChannelInterface::class];
        });
    }

    /**
     * @return QueueInterface
     * @throws \Throwable
     */
    public static function queue(): QueueInterface
    {
        return static::getStore(__FUNCTION__, function () {
            return new static::$container[QueueInterface::class];
        });
    }

    /**
     * @param string $name
     * @param callable $callback
     * @return mixed
     * @throws \Throwable
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

    /**
     * @param string $name
     * @return array
     */
    public static function getStores(string $name): array
    {
        $result = [];
        foreach (static::$stores as $storeMap) {
            if (isset($storeMap[$name])) {
                $result[] = $storeMap[$name];
            }
        }

        return $result;
    }
}