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
     * @return void
     */
    public function workerStop(): void;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @return int
     */
    public function getType(): int;

    /**
     * @return int
     */
    public function getWorkerNum(): int;

    /**
     * @param string|null $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getSettings(string $name = null, mixed $default = null): mixed;
}