<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface QueueInterface
{
    /**
     * @param JobInterface $job
     * @param array $data
     * @param float $delay
     * @return MessageInterface
     */
    public function push(JobInterface $job, array $data = [], float $delay = 0): MessageInterface;

    /**
     * @param string|null $name
     * @return QueueDriverInterface
     */
    public function driver(?string $name = null): QueueDriverInterface;
}