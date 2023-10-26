<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Scheduler;

use Larmias\Engine\Constants;
use Larmias\Engine\Swoole\Contracts\SchedulerInterface;

class Factory
{
    /**
     * @param int $mode
     * @return SchedulerInterface
     */
    public static function make(int $mode): SchedulerInterface
    {
        $class = match ($mode) {
            Constants::SCHEDULER_WORKER => WorkerScheduler::class,
            Constants::SCHEDULER_CO_WORKER => CoWorkerScheduler::class,
        };

        return new $class();
    }
}