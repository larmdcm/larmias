<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface QueueInterface
{
    /**
     * @param string $handler
     * @param array $data
     * @param int $delay
     * @return MessageInterface
     */
    public function push(string $handler, array $data = [], int $delay = 0): MessageInterface;

    /**
     * @param string|null $name
     * @return QueueDriverInterface
     */
    public function driver(?string $name = null): QueueDriverInterface;
}