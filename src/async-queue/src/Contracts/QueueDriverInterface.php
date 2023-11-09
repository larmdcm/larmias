<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface QueueDriverInterface
{
    /**
     * @param MessageInterface $message
     * @param int $delay
     * @return MessageInterface
     */
    public function push(MessageInterface $message, int $delay = 0): MessageInterface;

    /**
     * @param float $timeout
     * @param string|null $queue
     * @return MessageInterface|null
     */
    public function pop(float $timeout = 0, ?string $queue = null): ?MessageInterface;

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function ack(MessageInterface $message): bool;

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function fail(MessageInterface $message): bool;

    /**
     * @param MessageInterface $message
     * @param int $delay
     * @return MessageInterface|null
     */
    public function reload(MessageInterface $message, int $delay = 0): ?MessageInterface;

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function delete(MessageInterface $message): bool;

    /**
     * @param string|null $queue
     * @param float|null $waitTime
     * @return void
     */
    public function consumer(?string $queue = null, ?float $waitTime = null): void;

    /**
     * @param string|null $queue
     * @param string|null $type
     * @return bool
     */
    public function flush(?string $queue = null, ?string $type = null): bool;

    /**
     * @param string|null $queue
     * @return QueueStatusInterface
     */
    public function status(?string $queue = null): QueueStatusInterface;

    /**
     * @param string|null $queue
     * @return int
     */
    public function reloadFailMessage(?string $queue = null): int;

    /**
     * @param string|null $queue
     * @return int
     */
    public function reloadTimeoutMessage(?string $queue = null): int;

}