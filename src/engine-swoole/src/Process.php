<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Constants;
use Larmias\Engine\Event;
use Larmias\Engine\Timer;
use Throwable;
use function usleep;
use function sleep;

class Process extends Worker
{
    /**
     * @param int $workerId
     * @return void
     * @throws Throwable
     */
    public function workerStart(int $workerId): void
    {
        try {
            $this->start($workerId);
        } catch (Throwable $e) {
            $this->handleException($e);
        } finally {
            if ($this->isInProcessMode()) {
                Timer::clear();
            }
        }
    }

    /**
     * @param Throwable $e
     * @return void
     * @throws Throwable
     */
    public function handleException(Throwable $e): void
    {
        if ($this->isInProcessMode()) {
            $this->printException($e);
            return;
        }

        parent::handleException($e);
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function process(): void
    {
        if ($this->isInProcessMode()) {
            return;
        }

        while (ProcessManager::isRunning()) {
            try {
                if ($this->hasListen(Event::ON_WORKER)) {
                    $this->trigger(Event::ON_WORKER, [$this]);
                    usleep(1000);
                } else {
                    sleep(5);
                }
            } catch (Throwable $e) {
                $this->handleException($e);
            }
        }
    }

    /**
     * @return bool
     */
    protected function isInProcessMode(): bool
    {
        $mode = $this->getSettings()['mode'] ?? Constants::MODE_WORKER;
        return $mode == Constants::MODE_PROCESS;
    }
}