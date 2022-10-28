<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process\Worker;

use Larmias\WorkerS\Process\Manager;
use Larmias\WorkerS\Support\Helper;

abstract class Process
{   
    /**
     * @var int
     */
    const STATUS_RELOAD = 1;

    /**
     * @var int
     */
    const STATUS_STOP = 2;

    /**
     * 当前进程pid
     *
     * @var int
     */
    protected int $pid;

    /**
     * @var Manager
     */
    protected Manager $manager;

    /**
     * Process Constructor.
     *
     * @param Manager $manager
     * @param integer|null $pid
     */
    public function __construct(Manager $manager,?int $pid = null)
    {
        $this->manager = $manager;
        if (is_null($pid)) {
            $pid = \getmypid();
        }
        $this->pid = $pid;
    }

    /**
     * 设置进程id
     *
     * @param integer $pid
     * @return self
     */
    public function setPid(int $pid): self
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * 获取执行进程id
     *
     * @return integer
     */
    public function getPid(): int
    {
        return $this->pid;
    }

    /**
     * @return Manager
     */
    public function getManager(): Manager
    {
        return $this->manager;
    }

    /**
     * @return boolean
     */
    public function reload(): bool
    {
        if (Helper::isUnix() && $this->pid) {
            if (!\posix_kill($this->pid,\SIGUSR1)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return bool
     */
    public function stop(): bool
    {
        if (Helper::isUnix() && $this->pid) {
            if (!\posix_kill($this->pid,\SIGQUIT)) {
                return false;
            }
        }
        return true;
    }

    /**
     * 用户自定义信号处理 
     *
     * @return boolean
     */
    public function custom(): bool
    {
        if (Helper::isUnix() && $this->pid) {
            if (!\posix_kill($this->pid,\SIGUSR2)) {
                return false;
            }
        }
        return true;
    }
}