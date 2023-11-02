<?php

declare(strict_types=1);

namespace Larmias\Coroutine;

use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Facade\AbstractFacade;

/**
 * @method static ChannelInterface make(int $size = 0)
 */
class ChannelFactory extends AbstractFacade
{
    public static function getFacadeAccessor(): string|object
    {
        return ChannelFactoryInterface::class;
    }
}