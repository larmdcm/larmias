<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Contracts\DriverInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Swoole\Http\Server as HttpServer;
use Larmias\Engine\Swoole\Contracts\WorkerInterface;
use Larmias\Engine\Swoole\Coroutine\Channel;
use Swoole\Process as SwooleProcess;
use RuntimeException;
use const SIGUSR1;
use const SIGTERM;
use function function_exists;
use function get_class;

class Driver implements DriverInterface
{
    /**
     * @param KernelInterface $kernel
     * @return void
     * @throws \Throwable
     */
    public function run(KernelInterface $kernel): void
    {
        $manager = new Manager();
        foreach ($kernel->getWorkers() as $worker) {
            if (!($worker instanceof WorkerInterface)) {
                throw new RuntimeException(get_class($worker) . ' worker not instanceof ' . WorkerInterface::class);
            }
            $manager->addWorker($worker);
        }
        $manager->start();
    }

    /**
     * @param bool $force
     * @return void
     */
    public function stop(bool $force = true): void
    {
        if (function_exists('posix_getppid')) {
            SwooleProcess::kill(posix_getppid(), SIGTERM);
        }
    }

    /**
     * @param bool $force
     * @return void
     */
    public function restart(bool $force = true): void
    {
        $this->stop();
    }

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void
    {
        if (function_exists('posix_getppid')) {
            SwooleProcess::kill(posix_getppid(), SIGUSR1);
        }
    }

    /**
     * @return string|null
     */
    public function getTcpServerClass(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getUdpServerClass(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getHttpServerClass(): ?string
    {
        return HttpServer::class;
    }

    /**
     * @return string|null
     */
    public function getWebSocketServerClass(): ?string
    {
        return null;
    }

    /**
     * @return string|null
     */
    public function getProcessClass(): ?string
    {
        return Process::class;
    }

    /**
     * @return string|null
     */
    public function getEventLoopClass(): ?string
    {
        return EventLoop::class;
    }

    /**
     * @return string|null
     */
    public function getTimerClass(): ?string
    {
        return Timer::class;
    }

    /**
     * @return string|null
     */
    public function getSignalClass(): ?string
    {
        return Signal::class;
    }

    /**
     * @return string|null
     */
    public function getContextClass(): ?string
    {
        return Context::class;
    }

    /**
     * @return string|null
     */
    public function getCoroutineClass(): ?string
    {
        return Coroutine::class;
    }

    /**
     * @return string|null
     */
    public function getChannelClass(): ?string
    {
        return Channel::class;
    }
}