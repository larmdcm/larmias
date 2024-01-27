<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Process;

use Larmias\Contracts\SignalHandlerInterface;

class SignalManager
{
    /**
     * @var SignalHandlerInterface
     */
    protected static SignalHandlerInterface $signalHandler;

    /**
     * 已监听的信号列表
     * @var array
     */
    protected static array $signalMap = [];

    /**
     * 注册信号监听
     * @param int|array $signal
     * @param callable $callback
     * @return void
     */
    public static function on(int|array $signal, callable $callback): void
    {
        $signal = (array)$signal;
        foreach ($signal as $signalNo) {
            if (!isset(static::$signalMap[$signalNo])) {
                static::$signalMap[$signalNo] = true;
            }
            static::$signalHandler->onSignal($signalNo, $callback);
        }
    }

    /**
     * 移除信号监听
     * @param int|array $signal
     * @return void
     */
    public static function off(int|array $signal): void
    {
        $signal = (array)$signal;
        foreach ($signal as $signalNo) {
            if (isset(static::$signalMap[$signalNo])) {
                unset(static::$signalMap[$signalNo]);
            }
            static::$signalHandler->offSignal($signalNo);
        }
    }

    /**
     * 移除全部监听的信号
     * @return void
     */
    public static function offAll(): void
    {
        static::off(array_keys(static::$signalMap));
    }

    /**
     * @param SignalHandlerInterface $signalHandler
     * @return void
     */
    public static function setSignalHandler(SignalHandlerInterface $signalHandler): void
    {
        static::$signalHandler = $signalHandler;
    }
}