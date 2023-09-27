<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Contracts\SignalHandlerInterface;
use RuntimeException;
use function call_user_func_array;

/**
 * @method static bool onSignal($signal, callable $func)
 * @method static bool offSignal($signal)
 */
class SignalHandler
{
    /**
     * @var SignalHandlerInterface|null
     */
    protected static ?SignalHandlerInterface $signalHandler = null;

    /**
     * @param SignalHandlerInterface $signalHandler
     * @return void
     */
    public static function init(SignalHandlerInterface $signalHandler): void
    {
        static::$signalHandler = $signalHandler;
    }

    /**
     * @return SignalHandlerInterface
     */
    public static function getSignalHandler(): SignalHandlerInterface
    {
        return static::$signalHandler;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        if (static::$signalHandler === null) {
            throw new RuntimeException("not support: SignalHandler");
        }

        return call_user_func_array([static::$signalHandler, $name], $arguments);
    }
}