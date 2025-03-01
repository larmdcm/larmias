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
        Worker::getEventLoop()->onReadable($stream, $func);
        return true;
    }

    /**
     * @param resource $stream
     * @return bool
     */
    public function offReadable($stream): bool
    {
        Worker::getEventLoop()->offReadable($stream);
        return true;
    }

    /**
     * @param resource $stream
     * @param callable $func
     * @return bool
     */
    public function onWritable($stream, callable $func): bool
    {
        Worker::getEventLoop()->onWritable($stream, $func);
        return true;
    }

    /**
     * @param resource $stream
     * @return bool
     */
    public function offWritable($stream): bool
    {
        Worker::getEventLoop()->offWritable($stream);
        return true;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        Worker::getEventLoop()->run();
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        Worker::getEventLoop()->stop();
    }

    /**
     * @return EventInterface
     */
    public function getDriver(): EventInterface
    {
        return Worker::getEventLoop();
    }
}