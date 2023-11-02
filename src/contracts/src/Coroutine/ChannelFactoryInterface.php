<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

interface ChannelFactoryInterface
{
    /**
     * make Channel
     * @param int $size
     * @return ChannelInterface
     */
    public function make(int $size = 0): ChannelInterface;
}