<?php

declare(strict_types=1);

namespace Larmias\Engine\Sync;

use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Contracts\Sync\WaitGroupInterface;
use BadMethodCallException;
use InvalidArgumentException;

class WaitGroup implements WaitGroupInterface
{
    protected int $count = 0;

    protected ChannelInterface $channel;

    protected bool $waiting = false;

    public function __construct(ChannelFactoryInterface $factory)
    {
        $this->channel = $factory->create();
    }

    public function add(int $delta = 1): void
    {
        if ($this->waiting) {
            throw new BadMethodCallException('WaitGroup misuse: add called concurrently with wait');
        }
        $count = $this->count + $delta;
        if ($count < 0) {
            throw new InvalidArgumentException('WaitGroup misuse: negative counter');
        }
        $this->count = $count;
    }

    public function done(): void
    {
        $count = $this->count - 1;
        if ($count < 0) {
            throw new BadMethodCallException('WaitGroup misuse: negative counter');
        }
        $this->count = $count;
        if ($count === 0 && $this->waiting) {
            $this->channel->push(true);
        }
    }

    public function wait(float $timeout = -1): bool
    {
        if ($this->waiting) {
            throw new BadMethodCallException('WaitGroup misuse: reused before previous wait has returned');
        }

        if ($this->count > 0) {
            $this->waiting = true;
            $done = $this->channel->pop($timeout);
            $this->waiting = false;
            return $done;
        }
        return true;
    }

    public function count(): int
    {
        return $this->count;
    }
}