<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Contracts\DriverInterface;
use Larmias\Engine\Contracts\KernelInterface;
use Larmias\Engine\Swoole\Contracts\ServerInterface;
use Larmias\Engine\Swoole\Http\Server as HttpServer;

class Driver implements DriverInterface
{
    /**
     * @param KernelInterface $kernel
     * @return void
     */
    public function run(KernelInterface $kernel): void
    {
        $manager = new Manager();
        foreach ($kernel->getWorkers() as $worker) {
            $manager->addWorker($worker instanceof ServerInterface ? [$worker, 'initServer'] : [$worker, 'initProcess'],
                $worker->getSettings('worker_num', 1),
                $worker->getWorkerConfig()->getName()
            );
        }
        $manager->start();
    }

    /**
     * @param bool $force
     * @return void
     */
    public function stop(bool $force = true): void
    {
        // TODO: Implement stop() method.
    }

    /**
     * @param bool $force
     * @return void
     */
    public function restart(bool $force = true): void
    {
        // TODO: Implement restart() method.
    }

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void
    {
        // TODO: Implement reload() method.
    }

    public function getTcpServerClass(): ?string
    {
        return null;
    }

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

    public function getWebSocketServerClass(): ?string
    {
        return null;
    }

    public function getProcessClass(): ?string
    {
        return null;
    }

    public function getEventLoopClass(): ?string
    {
        return null;
    }

    public function getTimerClass(): ?string
    {
        return null;
    }

    public function getSignalClass(): ?string
    {
        return null;
    }

    public function getContextClass(): ?string
    {
        return null;
    }
}