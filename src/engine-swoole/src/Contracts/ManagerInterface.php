<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Contracts;

interface ManagerInterface
{
    /** @var string */
    public const MODE_WORKER = 'worker';

    /** @var string */
    public const MODE_CO_WORKER = 'coWorker';

    /**
     * @param WorkerInterface $worker
     * @return ManagerInterface
     */
    public function addWorker(WorkerInterface $worker): ManagerInterface;

    /**
     * @return void
     */
    public function start(): void;
}