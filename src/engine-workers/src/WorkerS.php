<?php

declare(strict_types=1);

namespace Larmias\Engine\WorkerS;

use Larmias\Engine\Contracts\DriverInterface;
use Larmias\WorkerS\Manager;

class WorkerS implements DriverInterface
{
    /**
     * @return void
     * @throws \Throwable
     */
    public function run(): void
    {
        Manager::runAll();
    }

    /**
     * @return string
     */
    public function getHttpServerClass(): string
    {
        return HttpServer::class;
    }
}