<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Contracts\DriverInterface;
use Larmias\Engine\Contracts\KernelInterface;

class Driver implements DriverInterface
{
    /**
     * @param \Larmias\Engine\Contracts\KernelInterface $kernel
     */
    public function run(KernelInterface $kernel): void
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
    public function getTcpServerClass(): string
    {
        return \Larmias\Engine\WorkerMan\Tcp\Server::class;
    }

    /**
     * @return string
     */
    public function getHttpServerClass(): string
    {
        return \Larmias\Engine\WorkerMan\Http\Server::class;
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
    public function getEventLoopClass(): string
    {
        return EventLoop::class;
    }

    /**
     * @return string
     */
    public function getTimerClass(): string
    {
        return Timer::class;
    }

}