<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface QueueDriverInterface
{
    /**
     * @param MessageInterface $message
     * @param float $delay
     * @return MessageInterface
     */
    public function push(MessageInterface $message, float $delay = 0): MessageInterface;

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
     * @return bool
     */
    public function delete(MessageInterface $message): bool;

    /**
     * @return void
     */
    public function consumer(): void;

    /**
     * @param string|null $queue
     * @return bool
     */
    public function flush(?string $queue = null): bool;

    /**
     * @param string|null $queue
     * @return array
     */
    public function info(?string $queue = null): array;

    /**
     * @return int
     */
    public function restoreFailMessage(): int;
}