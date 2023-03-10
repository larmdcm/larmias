<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Swoole\Contracts\WorkerInterface;
use Larmias\Engine\Worker as BaseWorker;
use Swoole\Process;
use Throwable;
use function Larmias\Utils\format_exception;
use function Larmias\Utils\println;
use function getmypid;
use const SIGTERM;

class Worker extends BaseWorker implements WorkerInterface
{
    /**
     * @param int $workerId
     * @return void
     */
    public function workerStart(int $workerId): void
    {
        try {
            $this->start($workerId);
        } catch (Throwable $e) {
            $this->exceptionHandler($e);
        }
    }

    /**
     * @return void
     */
    public function process(): void
    {
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getWorkerConfig()->getName();
    }

    /**
     * @return int
     */
    public function getNum(): int
    {
        return (int)$this->getSettings('worker_num', 1);
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function exceptionHandler(Throwable $e): void
    {
        println(format_exception($e));
        Process::kill(getmypid(), SIGTERM);
    }
}