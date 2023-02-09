<?php

declare(strict_types=1);

namespace Larmias\Crontab\Contracts;

use Larmias\Crontab\Crontab;
use SplQueue;

interface SchedulerInterface
{
    /**
     * @param Crontab $crontab
     * @return SchedulerInterface
     */
    public function add(Crontab $crontab): SchedulerInterface;

    /**
     * @param array $list
     * @return SchedulerInterface
     */
    public function batch(array $list): SchedulerInterface;

    /**
     * @return void
     */
    public function run(): void;

    /**
     * @return SplQueue
     */
    public function schedule(): SplQueue;
}