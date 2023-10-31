<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Swoole\Contracts\WorkerInterface;
use Larmias\Engine\Worker as BaseWorker;
use Swoole\Process as SwooleProcess;
use Throwable;
use function Larmias\Support\format_exception;
use function Larmias\Support\println;
use const SIGTERM;

abstract class Worker extends BaseWorker implements WorkerInterface
{
    /**
     * @var array
     */
    protected static array $data = [
        'initBind' => false,
        'initReset' => false,
    ];

    /**
     * @param int $workerId
     * @return void
     */
    public function workerStart(int $workerId): void
    {
        try {
            $this->start($workerId);
        } catch (Throwable $e) {
            $this->handleException($e);
        }
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function bind(): void
    {
        if (static::$data['initBind']) {
            return;
        }
        static::$data['initBind'] = true;
        parent::bind();
    }

    /**
     * @return void
     */
    protected function reset(): void
    {
        if (static::$data['initReset']) {
            return;
        }
        static::$data['initReset'] = true;
        parent::reset();
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
    public function getWorkerNum(): int
    {
        return (int)$this->getSettings('worker_num', 1);
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->getWorkerConfig()->getType();
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function printException(Throwable $e): void
    {
        println(format_exception($e));
    }

    /**
     * @param Throwable $e
     * @return void
     */
    public function handleException(Throwable $e): void
    {
        $this->printException($e);
        if (function_exists('posix_getppid')) {
            SwooleProcess::kill(posix_getppid(), SIGTERM);
        }
    }
}