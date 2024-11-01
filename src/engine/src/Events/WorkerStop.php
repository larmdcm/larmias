<?php

declare(strict_types=1);

namespace Larmias\Engine\Events;

class WorkerStop
{
    /**
     * WorkerStop constructor.
     * @param int $workerId
     */
    public function __construct(public int $workerId = 1)
    {
    }
}