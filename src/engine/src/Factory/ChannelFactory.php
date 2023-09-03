<?php

declare(strict_types=1);

namespace Larmias\Engine\Factory;

use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Engine\Coroutine\Channel;

class ChannelFactory implements ChannelFactoryInterface
{
    /**
     * 创建Channel
     * @param int $size
     * @return ChannelInterface
     */
    public function create(int $size = 0): ChannelInterface
    {
        return Channel::create($size);
    }

    /**
     * 是否支持Channel
     * @return bool
     */
    public function isSupport(): bool
    {
        return Channel::isSupport();
    }
}