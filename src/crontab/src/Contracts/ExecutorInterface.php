<?php

declare(strict_types=1);

namespace Larmias\Crontab\Contracts;

use Larmias\Crontab\Crontab;

interface ExecutorInterface
{
    /**
     * @param Crontab $crontab
     * @return void
     */
    public function execute(Crontab $crontab): void;
}