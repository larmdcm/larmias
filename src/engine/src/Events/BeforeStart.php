<?php

declare(strict_types=1);

namespace Larmias\Engine\Events;

use Larmias\Engine\Contracts\WorkerInterface;

class BeforeStart
{
    /**
     * @param WorkerInterface $kernel
     */
    public function __construct(public WorkerInterface $kernel)
    {
    }
}