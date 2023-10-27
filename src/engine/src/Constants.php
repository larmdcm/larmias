<?php

declare(strict_types=1);

namespace Larmias\Engine;

class Constants
{
    /**
     * 默认运行模式
     * @var int
     */
    public const MODE_BASE = 1;

    /**
     * worker运行模式
     * @var int
     */
    public const MODE_WORKER = 2;

    /**
     * 进程调度器
     * @var int
     */
    public const SCHEDULER_WORKER = 1;

    /**
     * 进程池调度器
     * @var int
     */
    public const SCHEDULER_WORKER_POOL = 1;

    /**
     * 协程调度器
     * @var int
     */
    public const SCHEDULER_CO_WORKER = 3;
}