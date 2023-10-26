<?php

declare(strict_types=1);

namespace Larmias\Engine;

class Constants
{
    /**
     * 默认运行模式内部自动事件循环
     * @var int
     */
    public const MODE_WORKER = 1;

    /**
     * 单进程运行模式
     * @var int
     */
    public const MODE_PROCESS = 2;

    /**
     * 进程worker调度
     * @var int
     */
    public const SCHEDULER_WORKER = 1;

    /**
     * 协程worker调度
     * @var int
     */
    public const SCHEDULER_CO_WORKER = 2;
}