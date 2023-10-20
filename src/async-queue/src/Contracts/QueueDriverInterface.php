<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface QueueDriverInterface
{
    /**
     * @param MessageInterface $message
     * @param float $delay
     * @return string
     */
    public function push(MessageInterface $message, float $delay = 0): string;

    /**
     * @param float $timeout
     * @return MessageInterface|null
     */
    public function pop(float $timeout = 0): ?MessageInterface;

    /**
     * @return void
     */
    public function consumer(): void;

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function ack(MessageInterface $message): bool;

    /**
     * @param MessageInterface $message
     * @param bool $reload
     * @return bool
     */
    public function fail(MessageInterface $message, bool $reload = false): bool;

    /**
     * @param MessageInterface $message
     * @return bool
     */
    public function delete(MessageInterface $message): bool;

    /**
     * @return bool
     */
    public function clear(): bool;

    /**
     * @return array
     */
    public function status(): array;

    /**
     * @return int
     */
    public function restoreFailMessage(): int;
}