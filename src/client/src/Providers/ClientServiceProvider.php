<?php

declare(strict_types=1);

namespace Larmias\Client\Providers;

use Larmias\Client\TcpClient;
use Larmias\Framework\ServiceProvider;
use Larmias\Contracts\TimerInterface;
use Larmias\Contracts\EventLoopInterface;
use Throwable;

class ClientServiceProvider extends ServiceProvider
{
    /**
     * @return void
     * @throws Throwable
     */
    public function register(): void
    {
        if ($this->container->has(TimerInterface::class)) {
            TcpClient::setTimer($this->container->get(TimerInterface::class));
        }

        if ($this->container->has(EventLoopInterface::class)) {
            TcpClient::setEventLoop($this->container->get(EventLoopInterface::class));
        }
    }
}