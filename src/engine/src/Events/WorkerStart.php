<?php

declare(strict_types=1);

namespace Larmias\Engine\Events;

class WorkerStart
{
    /**
     * WorkerStart constructor.
     *
     * @param int $workerId
     */
    public function __construct(public int $workerId = 1)
    {
    }
}