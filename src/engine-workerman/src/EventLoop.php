<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerMan;

use Larmias\Contracts\EventLoopInterface;
use Workerman\Events\EventInterface;

class EventLoop implements EventLoopInterface
{
    /**
     * @param resource $stream
     * @param callable $func
     * @return bool
     */
    public function onReadable($stream, callable $func): bool
    {
        Worker::getEventLoop()->add($stream, EventInterface::EV_READ, $func);
        return true;
    }

    /**
     * @param resource $stream
     * @return bool
     */
    public function offReadable($stream): bool
    {
        Worker::getEventLoop()->del($stream, EventInterface::EV_READ);
        return true;
    }

    /**
     * @param resource $stream
     * @param callable $func
     * @return bool
     */
    public function onWritable($stream, callable $func): bool
    {
        Worker::getEventLoop()->add($stream, EventInterface::EV_WRITE, $func);
        return true;
    }

    /**
     * @param resource $stream
     * @return bool
     */
    public function offWritable($stream): bool
    {
        Worker::getEventLoop()->del($stream, EventInterface::EV_WRITE);
        return true;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        Worker::getEventLoop()->loop();
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        Worker::getEventLoop()->destroy();
    }
}