<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface QueueInterface
{
    /**
     * @param JobInterface $job
     * @param float $delay
     * @return string
     */
    public function push(JobInterface $job, array $data = [], float $delay = 0): string;

    /**
     * @param string|null $name
     * @return QueueDriverInterface
     */
    public function driver(?string $name = null): QueueDriverInterface;
}