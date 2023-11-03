<?php

declare(strict_types=1);

namespace Larmias\Crontab\Process;

use Larmias\Contracts\ConfigInterface;
use Larmias\Crontab\Contracts\SchedulerInterface;
use Larmias\Contracts\TimerInterface;
use function array_merge;

class CrontabProcess
{
    /**
     * @var array
     */
    protected array $config = [
        'enable' => true,
        'crontab' => [],
        'tick_interval' => 1000,
    ];

    /**
     * @param ConfigInterface $config
     * @param SchedulerInterface $scheduler
     * @param TimerInterface $timer
     */
    public function __construct(ConfigInterface $config, protected SchedulerInterface $scheduler, protected TimerInterface $timer)
    {
        $this->config = array_merge($this->config, $config->get('crontab', []));
        if (!empty($this->config['crontab'])) {
            $this->scheduler->batch($this->config['crontab']);
        }
    }

    /**
     * @return void
     */
    public function handle(): void
    {
        if (!$this->config['enable']) {
            return;
        }
        
        $this->timer->tick($this->config['tick_interval'], function () {
            $this->scheduler->run();
        });
    }
}