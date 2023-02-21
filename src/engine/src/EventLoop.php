<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Contracts\EventLoopInterface;

/**
 * @method static bool onReadable($stream, callable $func, array $args = [])
 * @method static bool offReadable($stream)
 * @method static bool onWritable($stream, callable $func, array $args = [])
 * @method static bool offWritable($stream)
 * @method static void run()
 * @method static void stop()
 */
class EventLoop
{
    /**
     * @var EventLoopInterface|null
     */
    protected static ?EventLoopInterface $eventLoop = null;

    /**
     * @param EventLoopInterface $eventLoop
     * @return void
     */
    public static function init(EventLoopInterface $eventLoop): void
    {
        static::$eventLoop = $eventLoop;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return call_user_func_array([static::$eventLoop, $name], $arguments);
    }
}