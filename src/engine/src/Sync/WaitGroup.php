<?php

declare(strict_types=1);

namespace Larmias\Engine\Sync;

use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Contracts\Sync\WaitGroupInterface;

class WaitGroup implements WaitGroupInterface
{
    protected int $count = 0;

    protected ChannelInterface $channel;

    public function __construct(ChannelFactoryInterface $factory)
    {
        $this->channel = $factory->create();
    }

    public function add(int $num = 1): void
    {
        $this->count += $num;
    }

    public function done(): void
    {
        $this->channel->push(true);
    }

    public function wait(): void
    {
        for ($i = 0; $i < $this->count; $i++) {
            $this->channel->pop();
        }
    }
}