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
     * @param bool $force
     * @return void
     */
    public function stop(bool $force): void
    {
        Worker::command(__FUNCTION__, $force);
    }

    /**
     * @param bool $force
     * @return void
     */
    public function restart(bool $force): void
    {
        Worker::command(__FUNCTION__, $force);
    }

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void
    {
        Worker::command(__FUNCTION__, $force);
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
    public function getSignalClass(): string
    {
        return Signal::class;
    }

    /**
     * @return string
     */
    public function getContextClass(): string
    {
        return Context::class;
    }
}