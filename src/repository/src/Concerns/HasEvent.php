<?php

declare(strict_types=1);

namespace Larmias\Repository\Concerns;

use Closure;

trait HasEvent
{
    /**
     * @var array
     */
    protected static $events = [];

    /**
     * @var array
     */
    protected static $hasEventBoot = [];

    /**
     * @return void
     */
    public static function hasEventsBoot(): void
    {
    }

    /**
     * 触发事件
     *
     * @param string $name
     * @param ...$params
     * @return mixed|null
     */
    public function fireEvent(string $name,...$params)
    {
        if (!isset(static::$hasEventBoot[static::class])) {
            static::hasEventsBoot();
            static::$hasEventBoot[static::class] = true;
        }
        $event = static::getFullEventName($name);
        if (!isset(static::$events[$event])) {
            return null;
        }
        array_unshift($params, $this);
        return call_user_func(static::$events[$event],...$params);
    }

    /**
     * 注册事件.
     *
     * @param string $name
     * @param Closure $callback
     * @return void
     */
    public static function registerEvent(string $name,Closure $callback): void
    {
        $event = static::getFullEventName($name);
        static::$events[$event] = $callback;
    }

    /**
     * @param Closure $callback
     * @return void
     */
    public static function creating(Closure $callback): void
    {
        static::registerEvent(__FUNCTION__,$callback);
    }

    /**
     * @param Closure $callback
     * @return void
     */
    public static function created(Closure $callback): void
    {
        static::registerEvent(__FUNCTION__,$callback);
    }

    /**
     * @param Closure $callback
     * @return void
     */
    public static function updating(Closure $callback): void
    {
        static::registerEvent(__FUNCTION__,$callback);
    }

    /**
     * @param Closure $callback
     * @return void
     */
    public static function updated(Closure $callback): void
    {
        static::registerEvent(__FUNCTION__,$callback);
    }

    /**
     * @param Closure $callback
     * @return void
     */
    public static function deleting(Closure $callback): void
    {
        static::registerEvent(__FUNCTION__,$callback);
    }

    /**
     * @param Closure $callback
     * @return void
     */
    public static function deleted(Closure $callback): void
    {
        static::registerEvent(__FUNCTION__,$callback);
    }

    /**
     * @param string $name
     * @return string
     */
    public static function getFullEventName(string $name): string
    {
        return static::class . ':' . $name;
    }
}