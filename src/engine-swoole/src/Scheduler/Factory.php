<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Scheduler;

use Larmias\Engine\Constants;
use Larmias\Engine\Swoole\Contracts\SchedulerInterface;
use RuntimeException;

class Factory
{
    /**
     * @param int $type
     * @return SchedulerInterface
     */
    public static function make(int $type): SchedulerInterface
    {
        $class = match ($type) {
            Constants::SCHEDULER_WORKER_POOL => WorkerPoolScheduler::class,
            Constants::SCHEDULER_CO_WORKER => CoWorkerScheduler::class,
            default => throw new RuntimeException('type error.')
        };

        return new $class();
    }
}