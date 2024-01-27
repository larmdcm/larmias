<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Constants;
use Larmias\Engine\Event;
use Throwable;

class Process extends Worker
{
    /**
     * @var int
     */
    protected int $loopSpanTime = 5;

    /**
     * @param Throwable $e
     * @return void
     * @throws Throwable
     */
    public function handleException(Throwable $e): void
    {
        if ($this->isInWorkerMode()) {
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
        if ($this->isInWorkerMode()) {
            return;
        }

        while (ProcessManager::isRunning()) {
            try {
                if ($this->hasListen(Event::ON_WORKER)) {
                    $this->trigger(Event::ON_WORKER, [$this]);
                    $this->timespan();
                } else {
                    sleep($this->loopSpanTime);
                }
            } catch (Throwable $e) {
                $this->handleException($e);
            }
        }
    }

    /**
     * @return bool
     */
    protected function isInWorkerMode(): bool
    {
        $mode = $this->getSettings()['mode'] ?? Constants::MODE_BASE;
        return $mode == Constants::MODE_WORKER;
    }
}