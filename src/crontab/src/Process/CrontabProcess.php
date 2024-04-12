<?php

declare(strict_types=1);

namespace Larmias\Crontab\Process;

use Larmias\Contracts\ConfigInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\Worker\OnWorkerHandleInterface;
use Larmias\Contracts\Worker\WorkerInterface;
use Larmias\Crontab\Contracts\SchedulerInterface;
use Larmias\Contracts\TimerInterface;
use function array_merge;

class CrontabProcess implements OnWorkerHandleInterface
{
    /**
     * @var array
     */
    protected array $config = [
        'enable' => true,
        'crontab' => [],
    ];

    /**
     * @param ContainerInterface $container
     * @param ContextInterface $context
     * @param SchedulerInterface $scheduler
     * @param TimerInterface $timer
     * @param ConfigInterface $config
     */
    public function __construct(
        protected ContainerInterface $container,
        protected ContextInterface   $context,
        protected SchedulerInterface $scheduler,
        protected TimerInterface     $timer,
        ConfigInterface              $config,
    )
    {
        $this->config = array_merge($this->config, $config->get('crontab', []));
        if (!empty($this->config['crontab'])) {
            $this->scheduler->batch($this->config['crontab']);
        }
    }

    /**
     * @param WorkerInterface $worker
     * @return void
     */
    public function onWorkerHandle(WorkerInterface $worker): void
    {
        if (!$this->config['enable']) {
            return;
        }

        $this->scheduler->run();
    }
}