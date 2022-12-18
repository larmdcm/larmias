<?php

declare(strict_types=1);

namespace Larmias\Engine\Process\Handler;

use Larmias\Engine\Contracts\WatcherInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class WorkerWatchReloadHandler
{
    /**
     * @param WorkerInterface $worker
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function handle(WorkerInterface $worker): void
    {
        $watch = $worker->getSettings('watch',[]);
        $enabled = $watch['enabled'] ?? false;
        if (!$enabled) {
            return;
        }
        /** @var WatcherInterface $watcher */
        $watcher = $worker->getContainer()->get($watch['driver'] ?? \Larmias\Engine\Watcher\Scan::class);
        $watcher->include($watch['includes'] ?? [])->watch(function (string $realpath) use ($worker) {
            $worker->getKernel()->getDriver()->reload();
        });
    }
}