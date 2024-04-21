<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Scheduler;

use Larmias\Engine\Swoole\Contracts\SchedulerInterface;
use Larmias\Engine\Swoole\Contracts\WorkerInterface;
use Swoole\Coroutine;
use Swoole\Coroutine\Scheduler;
use const SWOOLE_HOOK_ALL;

class CoWorkerScheduler extends AbstractScheduler
{
    /**
     * @param WorkerInterface[] $workers
     */
    public function __construct(protected array $workers = [])
    {
    }

    public function addWorker(WorkerInterface $worker): SchedulerInterface
    {
        $this->workers[] = $worker;
        return $this;
    }

    public function start(): void
    {
        $scheduler = new Scheduler();

        $scheduler->set([
            'hook_flags' => SWOOLE_HOOK_ALL,
        ]);

        $scheduler->add(function () {
            foreach ($this->workers as $workerId => $worker) {
                Coroutine::create(function () use ($workerId, $worker) {
                    $worker->workerStart($workerId);
                    $worker->process();
                    $worker->workerStop();
                });
            }
        });

        $scheduler->start();
    }
}