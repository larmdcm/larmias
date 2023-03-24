<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

interface ChannelFactoryInterface
{
    /**
     * @param int $size
     * @return ChannelInterface
     */
    public function create(int $size = 0): ChannelInterface;
}