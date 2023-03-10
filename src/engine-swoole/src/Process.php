<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole;

use Larmias\Engine\Event;
use Larmias\Engine\ProcessManager;
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
                usleep(1000);
            } catch (\Throwable $e) {
                $this->exceptionHandler($e);
            }
        }
    }
}