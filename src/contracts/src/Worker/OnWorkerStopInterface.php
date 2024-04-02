<?php

declare(strict_types=1);

namespace Larmias\Contracts\Worker;

interface OnWorkerStopInterface
{
    /**
     * Worker停止退出事件
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerStop(WorkerInterface $worker): void;
}