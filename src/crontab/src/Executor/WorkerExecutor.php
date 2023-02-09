<?php

declare(strict_types=1);

namespace Larmias\Crontab\Executor;

use Larmias\Crontab\Executor;
use Larmias\Crontab\Crontab;

class WorkerExecutor extends Executor
{
    /**
     * @param Crontab $crontab
     * @return void
     */
    public function execute(Crontab $crontab): void
    {
        $this->handle($crontab);
    }
}