<?php

declare(strict_types=1);

namespace Larmias\Contracts\Worker;

interface OnWorkerStartInterface
{
    /**
     * Worker启动事件
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerStart(WorkerInterface $worker): void;
}