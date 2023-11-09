<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface JobInterface
{
    /**
     * @return bool
     */
    public function ack(): bool;

    /**
     * @param int $delay
     * @return MessageInterface
     */
    public function reload(int $delay = 0): MessageInterface;

    /**
     * @return bool
     */
    public function fail(): bool;

    /**
     * @return bool
     */
    public function delete(): bool;

    /**
     * @return array
     */
    public function getData(): array;

    /**
     * @return int
     */
    public function getAttempts(): int;

    /**
     * @return int
     */
    public function getMaxAttempts(): int;

    /**
     * @return MessageInterface
     */
    public function getMessage(): MessageInterface;
}