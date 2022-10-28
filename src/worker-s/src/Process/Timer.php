<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process;

use Larmias\WorkerS\Process\Contracts\TimerInterface;
use InvalidArgumentException;
use Throwable;

class Timer implements TimerInterface
{
    /**
     * 定时器id
     *
     * @var integer
     */
    protected $timerId = 0;

    /**
     * @var TimerInterface|null
     */
    protected static ?TimerInterface $instance = null;

    /**
     * @var array
     */
    protected array $tasks = [];

    /**
     * @var array
     */
    protected array $taskStatus = [];

    /**
     * Timer __construct
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * 获取单例对象
     *
     * @return TimerInterface
     */
    public static function getInstance(): TimerInterface
    {
        if (is_null(static::$instance)) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * 初始化
     *
     * @return void
     */
    public function init(): void
    {
        if (\function_exists('pcntl_signal')) {
            \pcntl_signal(\SIGALRM, [$this, 'signalHandle'], false);
        }
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
        return $this->add($time,$func,$args,true);
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
        return $this->add($time,$func,$args,false);
    }

    /**
     * 清空指定定时器
     *
     * @param int $timerId
     * @return boolean
     */
    public function clearTimer(int $timerId): bool
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
     *
     * @return boolean
     */
    public function clearAllTimer(): bool
    {
        $this->tasks = $this->taskStatus = [];
        \pcntl_alarm(0);
        return true;
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function signalHandle(): void
    {
        \pcntl_alarm(1);
        $this->interval();
    }

    /**
     * 添加一个定时器
     *
     * @param float $time
     * @param callable $func
     * @param array $args
     * @param boolean $persistent
     * 
     * @throws InvalidArgumentException
     * @return int
     */
    protected function add(float $time,callable $func,array $args,bool $persistent = true): int
    {
        if ($time <= 0) {
            throw new InvalidArgumentException("timer interval time must be greater than 0");
        }
        if (empty($this->tasks)) {
            \pcntl_alarm(1);
        }
        $this->timerId = $this->timerId === \PHP_INT_MAX ? 1 : ++$this->timerId;
        $runTime = \time() + $time;
        if (!isset($this->tasks[$runTime])) {
            $this->tasks[$runTime] = [];
        }
        $this->tasks[$runTime][$this->timerId] = [$func,$args,$persistent,$time];
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

        $timeNow = \time();
        foreach ($this->tasks as $runTime => $tasks) {
            if ($timeNow < $runTime) {
                continue;
            }
            foreach ($tasks as $timerId => $task) {
                try {
                    [$func,$args,$persistent,$time] = $task;
                    $func($args,$timerId);
                    if ($persistent && isset($this->taskStatus[$timerId])) {
                        $newRuntime = \time() + $time;
                        if (!isset($this->tasks[$newRuntime])) {
                            $this->tasks[$newRuntime] = [];
                        }
                        $this->tasks[$newRuntime][$timerId] = $task;
                    }
                } catch (Throwable $e) {
                    throw $e;
                }
            }
            unset($this->tasks[$runTime]);
        }
    }
}