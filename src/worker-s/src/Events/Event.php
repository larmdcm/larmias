<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Events;

use EventBase;
use Event as LibEvent;

class Event implements EventInterface
{
    /**
     * @var EventBase
     */
    protected EventBase $eventBase;

    /**
     * @var array[int]LibEvent
     */
    protected array $readEvents = [];

    /**
     * @var array[int]LibEvent
     */
    protected array $writeEvents = [];

    /**
     * @var array[int]LibEvent
     */
    protected array $signalEvents = [];

    /**
     * @var array[int]LibEvent
     */
    protected array $timerEvents = [];

    /**
     * @var int
     */
    protected int $timerId = 0;


    /**
     * Select __construct.
     */
    public function __construct()
    {
        $this->eventBase  = new EventBase();
    }

    /**
     * 添加读事件
     *
     * @param resource $stream
     * @param callable $func
     * @param array    $args
     * @return bool
     */
    public function onReadable($stream,callable $func,array $args = []): bool
    {
        $streamId = $this->getStreamId($stream);
        $event = $this->makeEvent($stream,LibEvent::READ|LibEvent::PERSIST,$func,$args);
        if (!$event) {
            return false;
        }
        $this->readEvents[$streamId] = $event;
        return true;
    }

    /**
     * 移除读事件
     *
     * @param  resource $stream
     * @return boolean
     */
    public function offReadable($stream): bool
    {
        $streamId = $this->getStreamId($stream);
        if (isset($this->readEvents[$streamId])) {
            $this->readEvents[$streamId]->del();
            unset($this->readEvents[$streamId]);
        }
        return true;
    }

    /**
     * 添加写事件
     *
     * @param resource $stream
     * @param callable $func
     * @param array    $args
     * @return bool
     */
    public function onWritable($stream,callable $func,array $args = []): bool
    {
        $streamId = $this->getStreamId($stream);
        $event = $this->makeEvent($stream,LibEvent::WRITE|LibEvent::PERSIST,$func,$args);
        if (!$event) {
            return false;
        }
        $this->writeEvents[$streamId] = $event;
        return true;
    }

    /**
     * 移除写事件
     *
     * @param  resource $stream
     * @return boolean
     */
    public function offWritable($stream): bool
    {
        $streamId = $this->getStreamId($stream);
        if (isset($this->writeEvents[$streamId])) {
            $this->writeEvents[$streamId]->del();
            unset($this->writeEvents[$streamId]);
        }
        return true;
    }

    /**
     * @param integer $signal
     * @param callable $func
     * @return bool
     */
    public function onSignal(int $signal,callable $func): bool
    {
        $event = $this->makeEvent($signal,LibEvent::SIGNAL,$func);
        if (!$event) {
            return false;
        }
        $this->signalEvents[$signal] = $event;
        return true;
    }

    /**
     * @param integer $signal
     * @param callable $func
     * @return bool
     */
    public function offSignal(int $signal): bool
    {
        if (isset($this->signalEvents[$signal])) {
            $this->signalEvents[$signal]->del();
            unset($this->signalEvents[$signal]);
        }
        return true;
    }

    /**
     * @return void
     */
    public function dispatchSignal(): void
    {
    }

    /**
     * 定时器间隔触发
     *
     * @param float     $time
     * @param callable  $func
     * @param array     $args
     * @return integer
     */
    public function tick(float $time,callable $func,array $args = []): int
    {
        return $this->addTimer($time,$func,$args,true);
    }

    /**
     * 定时器延时触发 只会触发一次
     *
     * @param float    $time
     * @param callable $func
     * @param array    $args
     * @return integer
     */
    public function after(float $time,callable $func,array $args = []): int
    {
        return $this->addTimer($time,$func,$args,false);
    }

    /**
     * 清空指定定时器
     *
     * @param int $timerId
     * @return boolean
     */
    public function clearTimer(int $timerId): bool
    {
        if (isset($this->timerEvents[$timerId])) {
            $this->timerEvents[$timerId]->del();
            unset($this->timerEvents[$timerId]);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function clearAllTimer(): bool
    {
        foreach ($this->timerEvents as $timerId => $event) {
            $event->del();
            unset($this->timerEvents[$timerId]);
        }
        return true;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        $this->eventBase->loop();
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        $this->clearAllTimer();
        foreach ($this->signalEvents as $signal => $event) {
            $this->offSignal($signal);
        }
        $this->eventBase->exit();
    }

    /**
     * 添加定时器
     *
     * @param float $time
     * @param callable $func
     * @param array $args
     * @param boolean $persistent
     * @return int
     */
    protected function addTimer(float $time,callable $func,array $args,bool $persistent = true): int
    {
        $flags = LibEvent::TIMEOUT;
        if ($persistent) {
            $flags = LibEvent::TIMEOUT|LibEvent::PERSIST;
        }
        $timerId = $this->timerId === \PHP_INT_MAX ? 1 : ++$this->timerId;
        $event = $this->makeEvent([-1,$time],$flags,function () use ($func,$args,$timerId) {
            $func($args,$timerId);
        });
        if (!$event) {
            return 0;
        }
        $this->timerEvents[$timerId] = $event;
        return $timerId;
    }

    /**
     * make event.
     *
     * @param int|resource|array $fd
     * @param integer $flag
     * @param callable $callback
     * @param array $args
     * @return LibEvent|null
     */
    protected function makeEvent($fd,int $flag,callable $callback,array $args = []): ?LibEvent
    {  
        if (!is_array($fd)) {
            $fd = [$fd];
        }
        $event = new LibEvent($this->eventBase,$fd[0],$flag,$callback,$args);
        if (!$event) {
            return null;
        }
        $added = isset($fd[1]) ? $event->addTimer($fd[1]) : $event->add();
        if (!$added) {
            return null;
        }
        return $event;
    }

    /**
     * @param int|resource $stream
     * @return integer
     */
    protected function getStreamId($stream): int
    {
        return (int)$stream;
    }
}