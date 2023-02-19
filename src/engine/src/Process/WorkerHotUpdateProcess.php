<?php

declare(strict_types=1);

namespace Larmias\Engine\Process;

use Larmias\Engine\Contracts\WatcherInterface;
use Larmias\Engine\Contracts\WorkerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class WorkerHotUpdateProcess
{
    /**
     * @var WatcherInterface
     */
    protected WatcherInterface $watcher;

    /**
     * @param WorkerInterface $worker
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(protected WorkerInterface $worker)
    {
        $watch = $worker->getSettings('watch', []);
        $enabled = $watch['enabled'] ?? false;
        if (!$enabled) {
            return;
        }
        /** @var WatcherInterface $watcher */
        $watcher = $worker->getContainer()->get($watch['driver'] ?? \Larmias\Engine\Watcher\Scan::class);
        $this->watcher = $watcher->include($watch['includes'] ?? [])
            ->exclude($watch['excludes'] ?? [])
            ->excludeExt($watch['excludeExts'] ?? []);
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public function handle(WorkerInterface $worker): void
    {
        $this->watcher->watch(function (string $path, int $event) use ($worker) {
            $worker->getKernel()->getDriver()->reload();
        });
    }
}