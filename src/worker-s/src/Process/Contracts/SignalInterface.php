<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process\Contracts;

interface SignalInterface
{
    /**
     * @param integer $signal
     * @param callable $func
     * @return bool
     */
    public function onSignal(int $signal,callable $func): bool;

    /**
     * @param integer $signal
     * @return bool
     */
    public function offSignal(int $signal): bool;

    /**
     * @return void
     */
    public function dispatchSignal(): void;
}