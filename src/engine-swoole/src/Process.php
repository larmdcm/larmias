<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\ProcessManager;
use Larmias\Engine\Event;
use Throwable;
use function usleep;
use function sleep;

class Process extends Worker
{
    /**
     * @return void
     */
    public function process(): void
    {
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
}