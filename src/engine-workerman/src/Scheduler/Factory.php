<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan\Scheduler;

use Larmias\Engine\WorkerMan\Contracts\SchedulerInterface;
use Larmias\Engine\Constants;
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
            Constants::SCHEDULER_WORKER => WorkerScheduler::class,
            default => throw new RuntimeException('type error.')
        };

        return new $class();
    }
}