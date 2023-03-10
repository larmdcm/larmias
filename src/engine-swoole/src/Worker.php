<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Swoole\Contracts\WorkerInterface;
use Larmias\Engine\Worker as BaseWorker;
use Swoole\Process as SwooleProcess;
use Throwable;
use function Larmias\Utils\format_exception;
use function Larmias\Utils\println;
use const SIGTERM;

abstract class Worker extends BaseWorker implements WorkerInterface
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
        if (function_exists('posix_getppid')) {
            SwooleProcess::kill(posix_getppid(), SIGTERM);
        }
    }
}