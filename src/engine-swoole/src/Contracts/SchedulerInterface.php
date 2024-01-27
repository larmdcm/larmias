<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Contracts;

interface SchedulerInterface
{
    /**
     * @param WorkerInterface $worker
     * @return SchedulerInterface
     */
    public function addWorker(WorkerInterface $worker): SchedulerInterface;

    /**
     * 设置配置
     * @param array $settings
     * @return SchedulerInterface
     */
    public function set(array $settings): SchedulerInterface;

    /**
     * @return void
     */
    public function start(): void;

    /**
     * @param bool $force
     * @return void
     */
    public function stop(bool $force = true): void;

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void;
}