<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Contracts\DriverInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\WorkerMan\Tcp\Server as TcpServer;
use Larmias\Engine\WorkerMan\Udp\Server as UdpServer;
use Larmias\Engine\WorkerMan\Http\Server as HttpServer;
use Larmias\Engine\WorkerMan\WebSocket\Server as WebSocketServer;

class Driver implements DriverInterface
{
    /**
     * @param KernelInterface $kernel
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
        return TcpServer::class;
    }

    /**
     * @return string
     */
    public function getUdpServerClass(): string
    {
        return UdpServer::class;
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
    public function getWebSocketServerClass(): string
    {
        return WebSocketServer::class;
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

    /**
     * @return string
     */
    public function getContextClass(): string
    {
        return Context::class;
    }
}