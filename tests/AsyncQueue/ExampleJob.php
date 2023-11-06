<?php

namespace LarmiasTest\AsyncQueue;

use Larmias\AsyncQueue\Contracts\JobInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use Larmias\AsyncQueue\Contracts\QueueDriverInterface;

class ExampleJob implements JobInterface
{
    public function handle(MessageInterface $message, QueueDriverInterface $queueDriver): void
    {
        $queueDriver->ack($message);
    }
}