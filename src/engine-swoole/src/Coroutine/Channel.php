<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Coroutine;

use Larmias\Contracts\Coroutine\ChannelInterface;
use Swoole\Coroutine\Channel as SwooleChannel;
use RuntimeException;
use const SWOOLE_CHANNEL_CLOSED;
use const SWOOLE_CHANNEL_TIMEOUT;

class Channel implements ChannelInterface
{
    /**
     * @var bool
     */
    protected bool $closed = false;

    /**
     * @var SwooleChannel
     */
    protected SwooleChannel $channel;

    /**
     * @param int $size
     */
    public function __construct(protected int $size = 1)
    {
        $this->channel = new SwooleChannel(max(1, $this->size));
    }

    /**
     * @param mixed $data
     * @param float $timeout
     * @return bool
     */
    public function push(mixed $data, float $timeout = -1): bool
    {
        $this->channel->push($data, $timeout);
        return true;
    }

    /**
     * @param float $timeout
     * @return mixed
     */
    public function pop(float $timeout = -1): mixed
    {
        return $this->channel->pop($timeout);
    }

    /**
     * @return int
     */
    public function capacity(): int
    {
        return $this->channel->capacity;
    }

    /**
     * @return int
     */
    public function length(): int
    {
        return $this->channel->length();
    }

    /**
     * @return bool
     */
    public function isAvailable(): bool
    {
        return !$this->isClosing();
    }

    /**
     * @return bool
     */
    public function close(): bool
    {
        $this->closed = true;
        $this->channel->close();
        return true;
    }

    /**
     * @return bool
     */
    public function hasProducers(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    /**
     * @return bool
     */
    public function hasConsumers(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    /**
     * @return bool
     */
    public function isReadable(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    /**
     * @return bool
     */
    public function isWritable(): bool
    {
        throw new RuntimeException('Not supported.');
    }

    /**
     * @return bool
     */
    public function isClosing(): bool
    {
        return $this->closed || $this->channel->errCode === SWOOLE_CHANNEL_CLOSED;
    }

    /**
     * @return bool
     */
    public function isTimeout(): bool
    {
        return !$this->closed && $this->channel->errCode === SWOOLE_CHANNEL_TIMEOUT;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->channel->isEmpty();
    }

    /**
     * @return bool
     */
    public function isFull(): bool
    {
        return $this->channel->isFull();
    }
}