<?php

declare(strict_types=1);

namespace Larmias\Task\Enum;

class WorkerStatus
{
    /**
     * @var int
     */
    public const IDLE = 0;

    /**
     * @var int
     */
    public const RUNNING = 1;

    /**
     * @var int
     */
    public const STOP = 2;
}