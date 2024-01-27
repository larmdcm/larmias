<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Engine\Constants;
use Larmias\Engine\Contracts\DriverInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\WorkerMan\Contracts\WorkerInterface;
use Larmias\Engine\WorkerMan\Scheduler\Factory;
use Larmias\Engine\WorkerMan\Tcp\Server as TcpServer;
use Larmias\Engine\WorkerMan\Udp\Server as UdpServer;
use Larmias\Engine\WorkerMan\Http\Server as HttpServer;
use Larmias\Engine\WorkerMan\WebSocket\Server as WebSocketServer;
use Larmias\Engine\WorkerType;

class Driver implements DriverInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @param KernelInterface $kernel
     */
    public function run(KernelInterface $kernel): void
    {
        $settings = $kernel->getConfig()->getSettings();
        $mode = $settings['mode'] ?? Constants::MODE_BASE;
        if ($mode == Constants::MODE_WORKER) {
            $scheduler = Factory::make(Constants::SCHEDULER_WORKER);
            $scheduler->addWorker($this->getMainWorker($kernel->getWorkers()));
            $scheduler->start();
            return;
        }

        Worker::runAll();
    }

    /**
     * @param array $workers
     * @return WorkerInterface
     */
    protected function getMainWorker(array $workers): WorkerInterface
    {
        $workers = array_filter($workers, function (WorkerInterface $worker) {
            return $worker->getType() === WorkerType::WORKER_PROCESS;
        });

        return current($workers);
    }

    /**
     * @param bool $force
     * @return void
     */
    public function stop(bool $force = true): void
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
     * @param array $config
     * @return void
     */
    public function setConfig(array $config = []): void
    {
        $this->config = $config;
        Worker::initConfig($this->config);
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $name, mixed $default = null): mixed
    {
        return $this->config[$name] ?? $default;
    }

    /**
     * @return string|null
     */
    public function getTcpServerClass(): ?string
    {
        return TcpServer::class;
    }

    /**
     * @return string|null
     */
    public function getUdpServerClass(): ?string
    {
        return UdpServer::class;
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
        return WebSocketServer::class;
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
        return SignalHandler::class;
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
        return null;
    }

    /**
     * @return string|null
     */
    public function getChannelClass(): ?string
    {
        return null;
    }
}