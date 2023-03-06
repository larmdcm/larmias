<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Contracts\DriverInterface;
use Larmias\Engine\Contracts\KernelInterface;

class Driver implements DriverInterface
{
    public function run(KernelInterface $kernel): void
    {
        // TODO: Implement run() method.
    }

    public function stop(bool $force = true): void
    {
        // TODO: Implement stop() method.
    }

    public function restart(bool $force = true): void
    {
        // TODO: Implement restart() method.
    }

    public function reload(bool $force = true): void
    {
        // TODO: Implement reload() method.
    }

    public function getTcpServerClass(): string
    {
        return '';
    }

    public function getUdpServerClass(): string
    {
        return '';
    }

    public function getHttpServerClass(): string
    {
        return '';
    }

    public function getWebSocketServerClass(): string
    {
        return '';
    }

    public function getProcessClass(): string
    {
        return '';
    }

    public function getEventLoopClass(): string
    {
        return '';
    }

    public function getTimerClass(): string
    {
        return '';
    }

    public function getSignalClass(): string
    {
        return '';
    }

    public function getContextClass(): string
    {
        return '';
    }
}