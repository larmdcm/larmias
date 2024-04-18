<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Process\Timer;

use InvalidArgumentException;
use Larmias\Contracts\SignalHandlerInterface;
use Larmias\Contracts\TimerInterface;
use Throwable;

class AlarmTimer implements TimerInterface
{
    /**
     * @var int
     */
    protected int $timerId = 0;

    /**
     * @var array
     */
    protected array $tasks = [];

    /**
     * @var array
     */
    protected array $taskStatus = [];

    /**
     * @param SignalHandlerInterface $signalHandler
     */
    public function __construct(protected SignalHandlerInterface $signalHandler)
    {
        $this->signalHandler->onSignal(SIGALRM, [$this, 'signalHandle']);
    }

    /**
     * 定时器间隔触发
     * @param int $duration
     * @param callable $func
     * @param array $args
     * @return integer
     */
    public function tick(int $duration, callable $func, array $args = []): int
    {
        return $this->add($duration, $func, $args);
    }

    /**
     * 定时器延时触发 只触发一次
     * @param int $duration
     * @param callable $func
     * @param array $args
     * @return integer
     */
    public function after(int $duration, callable $func, array $args = []): int
    {
        return $this->add($duration, $func, $args, false);
    }

    /**
     * 清空指定定时器
     * @param int $timerId
     * @return boolean
     */
    public function del(int $timerId): bool
    {
        foreach ($this->tasks as $runTime => $tasks) {
            if (isset($tasks[$timerId])) {
                unset($this->tasks[$runTime][$timerId]);
            }
        }
        if (isset($this->taskStatus[$timerId])) {
            unset($this->taskStatus[$timerId]);
        }
        return true;
    }

    /**
     * 清空全部定时器
     * @return boolean
     */
    public function clear(): bool
    {
        $this->tasks = $this->taskStatus = [];
        \pcntl_alarm(0);
        return true;
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function signalHandle(): void
    {
        \pcntl_alarm(1);
        $this->interval();
    }

    /**
     * 添加一个定时器
     * @param int $time
     * @param callable $func
     * @param array $args
     * @param boolean $persistent
     * @return int
     * @throws InvalidArgumentException
     */
    protected function add(int $time, callable $func, array $args, bool $persistent = true): int
    {
        $time = intval($time / 1000);
        if ($time <= 0) {
            throw new InvalidArgumentException("timer interval time must be greater than 0");
        }
        if (empty($this->tasks)) {
            \pcntl_alarm(1);
        }
        $this->timerId = $this->timerId === PHP_INT_MAX ? 1 : ++$this->timerId;
        $runTime = time() + $time;
        if (!isset($this->tasks[$runTime])) {
            $this->tasks[$runTime] = [];
        }
        $this->tasks[$runTime][$this->timerId] = [$func, $args, $persistent, $time];
        $this->taskStatus[$this->timerId] = true;
        return $this->timerId;
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function interval(): void
    {
        if (empty($this->tasks)) {
            \pcntl_alarm(0);
            return;
        }

        $timeNow = time();
        foreach ($this->tasks as $runTime => $tasks) {
            if ($timeNow < $runTime) {
                continue;
            }
            foreach ($tasks as $timerId => $task) {
                [$func, $args, $persistent, $time] = $task;
                $func($args, $timerId);
                if ($persistent && isset($this->taskStatus[$timerId])) {
                    $newRuntime = time() + $time;
                    if (!isset($this->tasks[$newRuntime])) {
                        $this->tasks[$newRuntime] = [];
                    }
                    $this->tasks[$newRuntime][$timerId] = $task;
                }
            }
            unset($this->tasks[$runTime]);
        }
    }
}