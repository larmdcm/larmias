<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Contracts\EventLoopInterface;
use Swoole\Event;
use const SWOOLE_EVENT_READ;
use const SWOOLE_EVENT_WRITE;

class EventLoop implements EventLoopInterface
{
    /**
     * @var array
     */
    protected array $readEvents = [];

    /**
     * @var array
     */
    protected array $writeEvents = [];

    /**
     * {@inheritdoc}
     */
    public function onReadable($stream, callable $func): bool
    {
        $fd = (int)$stream;
        if (!isset($this->readEvents[$fd]) && !isset($this->writeEvents[$fd])) {
            Event::add($stream, $func, null, SWOOLE_EVENT_READ);
        } else {
            if (isset($this->writeEvents[$fd])) {
                Event::set($stream, $func, null, SWOOLE_EVENT_READ | SWOOLE_EVENT_WRITE);
            } else {
                Event::set($stream, $func, null, SWOOLE_EVENT_READ);
            }
        }
        $this->readEvents[$fd] = $stream;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function offReadable($stream): bool
    {
        $fd = (int)$stream;
        if (!isset($this->readEvents[$fd])) {
            return false;
        }
        unset($this->readEvents[$fd]);
        if (!isset($this->writeEvents[$fd])) {
            Event::del($stream);
            return true;
        }
        Event::set($stream, null, null, SWOOLE_EVENT_WRITE);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function onWritable($stream, callable $func): bool
    {
        $fd = (int)$stream;
        if (!isset($this->readEvents[$fd]) && !isset($this->writeEvents[$fd])) {
            Event::add($stream, null, $func, SWOOLE_EVENT_WRITE);
        } else {
            if (isset($this->readEvents[$fd])) {
                Event::set($stream, null, $func, SWOOLE_EVENT_WRITE | SWOOLE_EVENT_READ);
            } else {
                Event::set($stream, null, $func, SWOOLE_EVENT_WRITE);
            }
        }
        $this->writeEvents[$fd] = $stream;
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function offWritable($stream): bool
    {
        $fd = (int)$stream;
        if (!isset($this->writeEvents[$fd])) {
            return false;
        }
        unset($this->writeEvents[$fd]);
        if (!isset($this->readEvents[$fd])) {
            Event::del($stream);
            return true;
        }
        Event::set($stream, null, null, SWOOLE_EVENT_READ);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function run(): void
    {
        Event::wait();
    }

    /**
     * {@inheritdoc}
     */
    public function stop(): void
    {
        Event::exit();
    }
}