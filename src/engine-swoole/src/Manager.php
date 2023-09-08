<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Swoole\Contracts\WorkerInterface;
use Swoole\Process\Pool;
use Swoole\Constant;
use Swoole\Runtime;
use function count;
use const SWOOLE_IPC_UNIXSOCK;

class Manager
{
    /**
     * @param WorkerInterface[] $workers
     */
    public function __construct(protected array $workers = [])
    {
    }

    /**
     * @param WorkerInterface $worker
     * @return self
     */
    public function addWorker(WorkerInterface $worker): self
    {
        $workerNum = $worker->getWorkerNum();
        for ($i = 0; $i < $workerNum; $i++) {
            $this->workers[] = $worker;
        }
        return $this;
    }

    /**
     * @return void
     */
    public function start(): void
    {
        $pool = new Pool(count($this->workers), SWOOLE_IPC_UNIXSOCK, 0, true);
        $pool->on(Constant::EVENT_WORKER_START, function (Pool $pool, int $workerId) {
            $worker = $this->workers[$workerId];
            Runtime::enableCoroutine($worker->getSettings('enable_coroutine', true));
            $worker->workerStart($workerId);
            $worker->process();
        });
        $pool->start();
    }
}