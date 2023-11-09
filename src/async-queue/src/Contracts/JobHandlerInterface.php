<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Contracts;

interface JobHandlerInterface
{
    /**
     * @param JobInterface $job
     * @return void
     */
    public function handle(JobInterface $job): void;
}