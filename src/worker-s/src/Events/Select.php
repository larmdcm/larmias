<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Events;

use Larmias\WorkerS\Support\Helper;
use SplPriorityQueue;

use Throwable;

class Select implements EventInterface
{   
    /**
     * @var int
     */
    const EV_READ = 1;

    /**
     * @var int
     */
    const EV_WRITE = 2;

    /**
     * @var int
     */
    const EV_EXCEPT = 3;

    /**
     * @var callable[]
     */
    protected array $readEvents = [];

    /**
     * @var callable[]
     */
    protected array $writeEvents = [];

    /**
     * @var callable[]
     */
    protected array $exceptEvents = [];

    /**
     * @var resource[]
     */
    protected array $readFds = [];

    /**
     * @var resource[]
     */
    protected array $writeFds = [];

    /**
     * @var resource[]
     */
    protected array $exceptFds = [];

    /**
     * @var integer
     */
    protected int $timerId = 0;

    /**
     * @var array
     */
    protected array $timerEvents = [];

    /**
     * @var SplPriorityQueue
     */
    protected SplPriorityQueue $scheduler;

     /**
     * @var int
     */
    protected int $selectTimeout = 100000000;

    /**
     * @var boolean
     */
    protected bool $isStop = false;

    /**
     * @var array
     */
    protected array $signalEvents = [];

    /**
     * Select __construct.
     */
    public function __construct()
    {
        $this->scheduler = new SplPriorityQueue();
        $this->scheduler->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
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
        $this->readFds[$streamId]      = $stream;
        $this->readEvents[$streamId]   = [$func,[$stream,$args]];
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
        unset($this->readFds[$streamId]);
        unset($this->readEvents[$streamId]);
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
        $this->writeFds[$streamId]     = $stream;
        $this->writeEvents[$streamId]  = [$func,[$stream,$args]];
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
        unset($this->writeFds[$streamId]);
        unset($this->writeEvents[$streamId]);
        return true;
    }

    /**
     * 添加异常事件
     *
     * @param resource $stream
     * @param callable $func
     * @param array    $args
     * @return bool
     */
    public function onExcept($stream,callable $func,array $args = []): bool
    {
        $streamId = $this->getStreamId($stream);
        $this->exceptFds[$streamId]    = $stream;
        $this->exceptEvents[$streamId] = [$func,[$stream,$args]];
        return true;
    }

    /**
     * 移除异常事件
     *
     * @param  resource $stream
     * @return boolean
     */
    public function offExcept($stream): bool
    {
        $streamId = $this->getStreamId($stream);
        unset($this->exceptFds[$streamId]);
        unset($this->exceptEvents[$streamId]);
        return true;
    }

    /**
     * @return void
     */
    public function run(): void
    {
        if (Helper::isSupportAsyncSignal()) {
            \pcntl_async_signals(true);
        }

        while (!$this->isStop) {
            
            $this->dispatchSignal();

            $readFds   = $this->readFds;
            $writeFds  = $this->writeFds;
            $exceptFds = $this->exceptFds;


            if ($readFds || $writeFds || $exceptFds) {
                try {
                    @stream_select($readFds,$writeFds,$exceptFds,0,$this->selectTimeout);
                } catch (Throwable $e) {
                }
            } else {
                $this->selectTimeout >= 1 && usleep($this->selectTimeout);
            }

            if (!$this->scheduler->isEmpty()) {
                $this->interval();
            }

            foreach ($readFds as $fd) {
                $this->trigger($fd,self::EV_READ);
            }

            foreach ($writeFds as $fd) {
                $this->trigger($fd,self::EV_WRITE);
            }

            foreach ($exceptFds as $fd) {
                $this->trigger($fd,self::EV_EXCEPT);
            }
        }
    }

    /**
     * @param integer $signal
     * @param callable $func
     * @return bool
     */
    public function onSignal(int $signal,callable $func): bool
    {
        if (!Helper::isUnix()) {
            return false;
        }
        $this->signalEvents[$signal] = $func;
        \pcntl_signal($signal,$func);
        return true;
    }

    /**
     * @param integer $signal
     * @param callable $func
     * @return bool
     */
    public function offSignal(int $signal): bool
    {
        if (!Helper::isUnix()) {
            return false;
        }
        unset($this->signalEvents[$signal]);
        return \pcntl_signal($signal, SIG_IGN);
    }
    
    /**
     * @return void
     */
    public function dispatchSignal(): void
    {
        if (!Helper::isUnix()) {
            return;
        }
        if (\pcntl_async_signals()) {
            return;
        }
        \pcntl_signal_dispatch();
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
     * @param array $  args
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
            unset($this->timerEvents[$timerId]);
        }
        return true;
    }

    /**
     * @return bool
     */
    public function clearAllTimer(): bool
    {
        $this->scheduler = new SplPriorityQueue();
        $this->scheduler->setExtractFlags(SplPriorityQueue::EXTR_BOTH);
        $this->timerEvents = [];
        return true;
    }

    /**
     * @return void
     */
    public function stop(): void
    {
        $this->isStop  = true;
        foreach ($this->signalEvents as $signal => $event) {
            $this->offSignal($signal);
        }
        $this->clearAllTimer();
        $this->readFds = $this->writeFds = $this->exceptFds = $this->readEvents
            = $this->writeEvents = $this->exceptEvents = $this->signalEvents = [];
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
        $runTime = \microtime(true) + $time;
        $this->timerId = $this->timerId === \PHP_INT_MAX ? 1 : ++$this->timerId;
        $params = [$func,$args];
        if ($persistent) {
            $params[] = $time;
        }
        $this->timerEvents[$this->timerId] = $params;
        $this->scheduler->insert($this->timerId,-$runTime);
        $selectTimeout = ($runTime - \microtime(true)) * 1000000;
        $selectTimeout = $selectTimeout <= 0 ? 1 : (int)$selectTimeout;
        if ($this->selectTimeout > $selectTimeout) {
            $this->selectTimeout = $selectTimeout;
        }
        return $this->timerId;
    }

    /**
     * @return void
     */
    protected function interval(): void
    {
        while (!$this->scheduler->isEmpty()) {
            $schedulerData = $this->scheduler->top();
            $timerId = $schedulerData['data'];
            $runTime = -$schedulerData['priority'];
            $nowTime = \microtime(true);
            $this->selectTimeout = (int)(($runTime - $nowTime) * 1000000);
            // 如果取出来的最低运行时间都还没到的话 直接就返回
            if ($this->selectTimeout <= 0) {
                $this->scheduler->extract();

                if (!isset($this->timerEvents[$timerId])) {
                    continue;
                }

                // [func, args, timer_interval]
                $taskData = $this->timerEvents[$timerId];
                if (isset($taskData[2])) {
                    $runTime = $nowTime + $taskData[2];
                    $this->scheduler->insert($timerId, -$runTime);
                } else {
                    unset($this->timerEvents[$timerId]);
                }
                $taskData[0]($taskData[1],$timerId);
                continue;
            }
            return;
        }
        // 没有任务重置select timeout...
        $this->selectTimeout = 100000000;
    }

    /**
     * 事件触发
     *
     * @param resource|int $stream
     * @param integer      $event
     * @return void
     */
    protected function trigger($stream,int $event): void
    {
        $streamId = $this->getStreamId($stream);
        $events   = [];
        switch ($event) {
            case self::EV_READ:
                $events = $this->readEvents;
                break;
            case self::EV_WRITE:
                $events = $this->writeEvents;
                break;
            case self::EV_EXCEPT:
                $events = $this->exceptEvents;
                break;
        }
        if (!isset($events[$streamId])) {
            return;
        }
        $event = $events[$streamId];
        call_user_func_array($event[0],$event[1]);
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