<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Process\Worker;

use Swoole\Process;

class WorkerKeeper extends Worker
{
    /**
     * 重启进程
     * @return bool
     */
    public function reload(): bool
    {
        $pid = $this->getPid();
        return $pid && Process::kill($pid, SIGUSR1);
    }

    /**
     * 停止进程
     * @return bool
     */
    public function stop(): bool
    {
        $pid = $this->getPid();
        return $pid && Process::kill($pid, SIGTERM);
    }
}