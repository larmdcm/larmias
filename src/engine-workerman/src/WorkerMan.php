<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Contracts\DriverInterface;
use Workerman\Worker;

class WorkerMan implements DriverInterface
{
    /**
     * @param array $workers
     * @return void
     * @throws \Throwable
     */
    public function run(array $workers): void
    {
        Worker::runAll();
    }

    /**
     * @return void
     */
    public function reload(): void
    {
        if (extension_loaded('posix') && extension_loaded('pcntl')) {
            \posix_kill(\posix_getppid(), \SIGUSR1);
        }
    }

    /**
     * @return string
     */
    public function getHttpServerClass(): string
    {
        return HttpServer::class;
    }

    /**
     * @return string
     */
    public function getProcessClass(): string
    {
        return Process::class;
    }

    /**
     * @return string
     */
    public function getTimerClass(): string
    {
        return Timer::class;
    }
}