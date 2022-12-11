<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Contracts\DriverInterface;
use Workerman\Worker;

class WorkerMan implements DriverInterface
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function run(): void
    {
        Worker::runAll();
    }

    /**
     * @return void
     */
    public function reload(): void
    {
        Worker::reloadAllWorkers();
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
    public function getTimerClass(): string
    {
        return Timer::class;
    }
}