<?php

declare(strict_types=1);

namespace Larmias\Engine\Coroutine;

use Larmias\Contracts\Coroutine\ChannelInterface;
use RuntimeException;

class Channel
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
     * @param int $size
     * @return ChannelInterface
     */
    public static function create(int $size = 0): ChannelInterface
    {
        if (!static::support()) {
            throw new RuntimeException("not support: Channel");
        }
        return new static::$chClass($size);
    }

    /**
     * @return bool
     */
    public static function support(): bool
    {
        return static::$chClass !== null;
    }
}