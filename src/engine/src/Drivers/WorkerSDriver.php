<?php

declare(strict_types=1);

namespace Larmias\Engine\Drivers;

use Larmias\Engine\Contracts\DriverInterface;
use Larmias\WorkerS\Manager;

class WorkerSDriver implements DriverInterface
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function run(): void
    {
        Manager::runAll();
    }
}