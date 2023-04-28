<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\ProcessManager;
use Larmias\Engine\Event;
use function usleep;

class Process extends Worker
{
    /**
     * @return void
     */
    public function process(): void
    {
        while (ProcessManager::isRunning()) {
            try {
                $this->trigger(Event::ON_WORKER, [$this]);
                usleep(100);
            } catch (\Throwable $e) {
                $this->exceptionHandler($e);
            }
        }
    }
}