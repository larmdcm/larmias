<?php

declare(strict_types=1);

namespace Larmias\Contracts\Coroutine;

interface ChannelInterface
{
    /**
     * @param mixed $data
     * @param float $timeout
     * @return bool
     */
    public function push(mixed $data, $timeout = -1);

    /**
     * @param float $timeout
     * @return mixed
     */
    public function pop($timeout = -1);

    /**
     * @return int
     */
    public function capacity(): int;

    /**
     * @return int
     */
    public function length(): int;

    /**
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * @return bool
     */
    public function close(): bool;

    /**
     * @return bool
     */
    public function hasProducers(): bool;

    /**
     * @return bool
     */
    public function hasConsumers(): bool;

    /**
     * @return bool
     */
    public function isReadable(): bool;

    /**
     * @return bool
     */
    public function isWritable(): bool;

    /**
     * @return bool
     */
    public function isClosing(): bool;

    /**
     * @return bool
     */
    public function isTimeout(): bool;

    /**
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * @return bool
     */
    public function isFull(): bool;
}