<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Contracts\EventLoopInterface;
use Workerman\Events\EventInterface;

class EventLoop implements EventLoopInterface
{
    public function onReadable($stream, callable $func, array $args = []): bool
    {
        return Worker::getEventLoop()->add($stream, EventInterface::EV_READ, $func, $args);
    }

    public function offReadable($stream): bool
    {
        return Worker::getEventLoop()->del($stream, EventInterface::EV_READ);
    }

    public function onWritable($stream, callable $func, array $args = []): bool
    {
        return Worker::getEventLoop()->add($stream, EventInterface::EV_WRITE, $func, $args);
    }

    public function offWritable($stream): bool
    {
        return Worker::getEventLoop()->del($stream, EventInterface::EV_WRITE);
    }

    public function run(): void
    {
        Worker::getEventLoop()->loop();
    }

    public function stop(): void
    {
        Worker::getEventLoop()->destroy();
    }
}