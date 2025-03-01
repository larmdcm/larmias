<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Coroutine;

use Larmias\Contracts\Coroutine\ChannelInterface;
use RuntimeException;
use Workerman\Coroutine\Channel as WorkermanChannel;

class Channel implements ChannelInterface
{
    protected WorkermanChannel $channel;

    protected bool $closed = false;

    public function __construct(protected int $size = 1)
    {
        $this->channel = new WorkermanChannel(max($this->size, 1));
    }

    public function push(mixed $data, float $timeout = -1): bool
    {
        return $this->channel->push($data, $timeout);
    }

    public function pop(float $timeout = -1): mixed
    {
        return $this->channel->pop($timeout);
    }

    public function capacity(): int
    {
        return $this->channel->getCapacity();
    }

    public function length(): int
    {
        return $this->channel->length();
    }

    public function isAvailable(): bool
    {
        return !$this->isClosing();
    }

    public function close(): bool
    {
        $this->channel->close();
        $this->closed = true;
        return true;
    }

    public function hasProducers(): bool
    {
        return $this->channel->hasProducers();
    }

    public function hasConsumers(): bool
    {
        return $this->channel->hasConsumers();
    }

    public function isReadable(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    public function isWritable(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    public function isClosing(): bool
    {
        return $this->closed;
    }

    public function isTimeout(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    public function isEmpty(): bool
    {
        return $this->length() <= 0;
    }

    public function isFull(): bool
    {
        return $this->length() >= $this->capacity();
    }
}