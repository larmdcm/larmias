<?php

declare(strict_types=1);

namespace Larmias\Pool;

use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Contracts\Pool\ConnectionInterface;
use SplQueue;

class Channel
{
    /**
     * @var ChannelInterface|null
     */
    protected ?ChannelInterface $channel = null;

    /**
     * @var SplQueue|null
     */
    protected ?SplQueue $queue = null;

    /**
     * @param ContextInterface $context
     * @param ChannelFactoryInterface $channelFactory
     * @param int $size
     */
    public function __construct(protected ContextInterface $context, protected ChannelFactoryInterface $channelFactory, protected int $size)
    {
        if ($this->isCoroutine()) {
            $this->channel = $this->channelFactory->make($this->size);
        } else {
            $this->queue = new SplQueue();
        }
    }

    /**
     * @param ConnectionInterface $connection
     * @return bool
     */
    public function push(ConnectionInterface $connection): bool
    {
        if ($this->channel) {
            return $this->channel->push($connection, 0.001);
        }
        $this->queue->push($connection);
        return true;
    }

    /**
     * @param float $timeout
     * @return ConnectionInterface|null
     */
    public function pop(float $timeout = -1): ?ConnectionInterface
    {
        $connection = $this->channel ? $this->channel->pop($timeout) : $this->queue->shift();
        return $connection ?: null;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->channel ? $this->channel->isEmpty() : $this->queue->isEmpty();
    }

    /**
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->channel ? $this->channel->isFull() : $this->queue->count() >= $this->size;
    }

    /**
     * @return int
     */
    public function length(): int
    {
        return $this->channel ? $this->channel->length() : $this->queue->count();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        $this->channel?->close();
        return true;
    }

    /**
     * @return bool
     */
    protected function isCoroutine(): bool
    {
        return $this->context->inCoroutine() && !$this->context->inFiber();
    }
}