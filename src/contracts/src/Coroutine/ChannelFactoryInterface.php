<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

interface ChannelFactoryInterface
{
    /**
     * 创建Channel
     * @param int $size
     * @return ChannelInterface
     */
    public function create(int $size = 0): ChannelInterface;

    /**
     * 是否支持Channel
     * @return bool
     */
    public function isSupport(): bool;
}