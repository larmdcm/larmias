<?php

declare(strict_types=1);

namespace Larmias\Engine;

use Larmias\Contracts\SignalInterface;

/**
 * @method static bool onSignal($signal, callable $func)
 * @method static bool offSignal($signal)
 */
class Signal
{
    /**
     * @var SignalInterface|null
     */
    protected static ?SignalInterface $signal = null;

    /**
     * @param SignalInterface $signal
     * @return void
     */
    public static function init(SignalInterface $signal): void
    {
        static::$signal = $signal;
    }

    /**
     * @param string $name
     * @param array $arguments
     * @return mixed
     */
    public static function __callStatic(string $name, array $arguments): mixed
    {
        return \call_user_func_array([static::$signal, $name], $arguments);
    }
}