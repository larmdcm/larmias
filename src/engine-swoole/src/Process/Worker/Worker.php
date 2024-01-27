<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Process\Worker;

use Larmias\Engine\Swoole\Process\WorkerPool;
use Swoole\Process;

class Worker
{
    /**
     * 退出代码
     * @var int
     */
    protected int $exitCode = 0;

    /**
     * 接收到的信号
     * @var int
     */
    protected int $signal = 0;

    /**
     * 退出代码
     * @var int
     */
    public const EXIT_NORMAL = 0;

    /**
     * 重启代码
     * @var int
     */
    public const EXIT_RELOAD = 100;

    /**
     * 停止代码
     * @var int
     */
    public const EXIT_STOP = 101;

    /**
     * @param WorkerPool $workerPool
     * @param Process $process
     * @param int $id
     */
    public function __construct(protected WorkerPool $workerPool, protected Process $process, protected int $id)
    {
    }

    /**
     * 工作进程id
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * 工作进程pid
     * @return int
     */
    public function getPid(): int
    {
        return $this->process->pid;
    }

    /**
     * @param array $waitResult
     * @return void
     */
    public function setFromWaitResult(array $waitResult): void
    {
        $this->exitCode = $waitResult['code'];
        $this->signal = $waitResult['signal'];
    }

    /**
     * @return int
     */
    public function getExitCode(): int
    {
        return $this->exitCode;
    }

    /**
     * @param int $exitCode
     */
    public function setExitCode(int $exitCode): void
    {
        $this->exitCode = $exitCode;
    }

    /**
     * @return int
     */
    public function getSignal(): int
    {
        return $this->signal;
    }

    /**
     * @param int $signal
     */
    public function setSignal(int $signal): void
    {
        $this->signal = $signal;
    }

    /**
     * 退出程序
     * @param int|null $code
     * @return void
     */
    public function exit(?int $code = null): void
    {
        if ($code === null) {
            $code = $this->exitCode;
        }

        $this->process->exit($code);
    }
}