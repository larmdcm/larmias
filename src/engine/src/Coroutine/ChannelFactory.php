<?php

declare(strict_types=1);

namespace Larmias\Engine\Coroutine;

use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use RuntimeException;

class ChannelFactory implements ChannelFactoryInterface
{
    /**
     * @var string|null
     */
    protected static ?string $chClass = null;

    /**
     * @param string|null $chClass
     * @return void
     */
    public static function init(?string $chClass = null): void
    {
        static::$chClass = $chClass;
    }

    /**
     * make channel
     * @param int $size
     * @return ChannelInterface
     */
    public function make(int $size = 0): ChannelInterface
    {
        if (!static::isSupport()) {
            throw new RuntimeException("not support: Channel");
        }

        return new static::$chClass($size);
    }

    /**
     * 是否支持Channel
     * @return bool
     */
    public static function isSupport(): bool
    {
        return static::$chClass !== null;
    }
}