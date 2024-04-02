<?php

declare(strict_types=1);

namespace Larmias\Contracts\Worker;

interface OnWorkerHandleInterface
{
    /**
     * Worker处理事件
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerHandle(WorkerInterface $worker): void;
}