<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\Coroutine\ChannelInterface;
use Swoole\Coroutine\Channel as SwooleChannel;
use RuntimeException;
use const SWOOLE_CHANNEL_CLOSED;
use const SWOOLE_CHANNEL_TIMEOUT;

class Channel extends SwooleChannel implements ChannelInterface
{
    /**
     * @var bool
     */
    protected bool $closed = false;

    /**
     * @param mixed $data
     * @param float $timeout
     * @return bool
     */
    public function push(mixed $data, float $timeout = -1): bool
    {
        return parent::push($data, $timeout);
    }

    /**
     * @param float $timeout
     * @return mixed
     */
    public function pop(float $timeout = -1): mixed
    {
        return parent::pop($timeout);
    }

    /**
     * @return int
     */
    public function capacity(): int
    {
        return $this->capacity;
    }

    /**
     * @return int
     */
    public function length(): int
    {
        return parent::length();
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
        return parent::close();
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
        return $this->closed || $this->errCode === SWOOLE_CHANNEL_CLOSED;
    }

    /**
     * @return bool
     */
    public function isTimeout(): bool
    {
        return !$this->closed && $this->errCode === SWOOLE_CHANNEL_TIMEOUT;
    }

    /**
     * @return bool
     */
    public function isEmpty(): bool
    {
        return parent::isEmpty();
    }

    /**
     * @return bool
     */
    public function isFull(): bool
    {
        return parent::isFull();
    }
}