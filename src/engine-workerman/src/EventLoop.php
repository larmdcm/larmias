<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Contracts\EventLoopInterface;
use Workerman\Events\EventInterface;

class EventLoop implements EventLoopInterface
{
    public function onReadable($stream, callable $func): bool
    {
        Worker::getEventLoop()->add($stream, EventInterface::EV_READ, $func);
        return true;
    }

    public function offReadable($stream): bool
    {
        Worker::getEventLoop()->del($stream, EventInterface::EV_READ);
        return true;
    }

    public function onWritable($stream, callable $func): bool
    {
        Worker::getEventLoop()->add($stream, EventInterface::EV_WRITE, $func);
        return true;
    }

    public function offWritable($stream): bool
    {
        Worker::getEventLoop()->del($stream, EventInterface::EV_WRITE);
        return true;
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