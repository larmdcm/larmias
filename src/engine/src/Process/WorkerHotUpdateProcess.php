<?php

declare(strict_types=1);

namespace Larmias\Engine\Process;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\FileWatcherInterface;
use Larmias\Contracts\Worker\OnWorkerStartInterface;
use Larmias\Contracts\Worker\WorkerInterface;

class WorkerHotUpdateProcess implements OnWorkerStartInterface
{
    /**
     * @var FileWatcherInterface
     */
    protected FileWatcherInterface $watcher;

    /**
     * @var bool
     */
    protected bool $enabled = false;

    /**
     * @param ContainerInterface $container
     * @param WorkerInterface $worker
     */
    public function __construct(protected ContainerInterface $container, protected WorkerInterface $worker)
    {
        $config = $this->worker->getSettings('hotUpdate', []);
        $this->enabled = $config['enabled'] ?? false;
        if (!$this->enabled) {
            return;
        }
        /** @var FileWatcherInterface $watcher */
        $watcher = $this->container->make(FileWatcherInterface::class, ['config' => $config], true);
        $this->watcher = $watcher;
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerStart(WorkerInterface $worker): void
    {
        if (!$this->enabled) {
            return;
        }

        $this->watcher->watch(function (string $path, int $event) use ($worker) {
            $worker->getKernel()->reload();
        });
    }
}