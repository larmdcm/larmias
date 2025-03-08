<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Scheduler;

use Larmias\Engine\Constants;
use Larmias\Engine\Swoole\Contracts\SchedulerInterface;
use Larmias\Engine\Swoole\Contracts\WorkerInterface;
use Larmias\Engine\Swoole\Process\Worker;
use Larmias\Engine\Swoole\Process\WorkerPool;
use Swoole\Constant;
use function count;

class WorkerPoolScheduler extends AbstractScheduler
{
    /**
     * @param WorkerInterface[] $workers
     */
    public function __construct(protected array $workers = [])
    {
    }

    /**
     * @param WorkerInterface $worker
     * @return SchedulerInterface
     */
    public function addWorker(WorkerInterface $worker): SchedulerInterface
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
        $pool = $this->makeWorkerPool();
        $pool->on(Constant::EVENT_WORKER_START, function (Worker $process) {
            $workerId = $process->getId();
            $worker = $this->workers[$workerId];
            $worker->workerStart($workerId);
            $worker->process();
            $worker->workerStop();
        });
        $pool->start();
    }

    /**
     * @param bool $force
     * @return void
     */
    public function stop(bool $force = true): void
    {
        $this->makeWorkerPool()->stop();
    }

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void
    {
        $this->makeWorkerPool()->reload();
    }

    /**
     * @return WorkerPool
     */
    protected function makeWorkerPool(): WorkerPool
    {
        $pool = new WorkerPool();
        $pool->set([
            'log_debug' => $this->settings[Constants::OPTION_LOG_DEBUG] ?? false,
            'daemonize' => $this->settings[Constants::OPTION_DAEMONIZE] ?? false,
            'worker_num' => count($this->workers),
            'enable_coroutine' => $this->settings[Constants::OPTION_ENABLE_COROUTINE] ?? true,
            'max_wait_time' => $this->settings[Constants::OPTION_MAX_WAIT_TIME] ?? 3,
            'stop_wait_time' => $this->settings[Constants::OPTION_STOP_WAIT_TIME] ?? 3,
            'worker_auto_recover' => $this->settings[Constants::OPTION_WORKER_AUTO_RECOVER] ?? true,
            'pid_file' => $this->settings[Constants::OPTION_PID_FILE] ?? null,
            'log_file' => $this->settings[Constants::OPTION_LOG_FILE] ?? null,
        ]);
        return $pool;
    }
}