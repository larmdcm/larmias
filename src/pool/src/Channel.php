<?php

declare(strict_types=1);

namespace Larmias\Pool;

use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Contracts\Pool\ConnectionInterface;
use Larmias\Engine\Coroutine\Channel as EngineChannel;
use Larmias\Engine\Coroutine;
use SplQueue;

class Channel
{
    /**
     * @var ChannelInterface|null
     */
    protected ?ChannelInterface $channel = null;

    /**
     * @var SplQueue
     */
    protected SplQueue $queue;

    /**
     * @param int $size
     */
    public function __construct(protected int $size)
    {
        $this->queue = new SplQueue();
        if ($this->isCoroutine()) {
            $this->channel = EngineChannel::create($size);
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
        return Coroutine::id() > 0;
    }
}