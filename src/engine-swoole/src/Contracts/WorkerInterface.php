<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Contracts;

interface WorkerInterface
{
    /**
     * @param int $workerId
     * @return void
     */
    public function workerStart(int $workerId): void;

    /**
     * @return void
     */
    public function process(): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getNum(): int;
}