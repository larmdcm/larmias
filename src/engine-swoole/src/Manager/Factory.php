<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Manager;

use Larmias\Engine\Swoole\Contracts\ManagerInterface;

class Factory
{
    /**
     * @param string $name
     * @return ManagerInterface
     */
    public static function make(string $name): ManagerInterface
    {
        $class = match ($name) {
            ManagerInterface::MODE_WORKER => WorkerManager::class,
            ManagerInterface::MODE_CO_WORKER => CoWorkerManager::class,
        };

        return new $class();
    }
}