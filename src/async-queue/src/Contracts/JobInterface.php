<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface JobInterface
{
    /**
     * @param MessageInterface $message
     * @param QueueDriverInterface $queueDriver
     * @return void
     */
    public function handle(MessageInterface $message, QueueDriverInterface $queueDriver): void;
}