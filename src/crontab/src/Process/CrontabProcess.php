<?php

declare(strict_types=1);

namespace Larmias\Crontab\Process;

use Larmias\Contracts\ConfigInterface;
use Larmias\Crontab\Contracts\SchedulerInterface;
use Larmias\Engine\Timer;

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
     */
    public function __construct(ConfigInterface $config, protected SchedulerInterface $scheduler)
    {
        $this->config = \array_merge($this->config, $config->get('crontab', []));
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
        Timer::tick($this->config['tick_interval'], function () {
            $this->scheduler->run();
        });
    }
}