<?php

namespace LarmiasTest\AsyncQueue;

use Larmias\AsyncQueue\Contracts\JobHandlerInterface;
use Larmias\AsyncQueue\Contracts\JobInterface;

class ExampleJobHandler implements JobHandlerInterface
{
    public function handle(JobInterface $job): void
    {
        $job->ack();
    }
}