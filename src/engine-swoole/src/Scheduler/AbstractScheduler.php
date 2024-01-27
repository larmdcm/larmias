<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Scheduler;

use Larmias\Engine\Swoole\Contracts\SchedulerInterface;

abstract class AbstractScheduler implements SchedulerInterface
{
    /**
     * 配置项
     * @var array
     */
    protected array $settings = [];

    /**
     * 设置配置
     * @param array $settings
     * @return SchedulerInterface
     */
    public function set(array $settings = []): SchedulerInterface
    {
        $this->settings = $settings;
        return $this;
    }

    /**
     * @param bool $force
     * @return void
     */
    public function stop(bool $force = true): void
    {

    }

    /**
     * @param bool $force
     * @return void
     */
    public function reload(bool $force = true): void
    {
    }
}