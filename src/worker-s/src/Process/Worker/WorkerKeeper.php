<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process\Worker;

use Larmias\WorkerS\Process\Manager;

class WorkerKeeper extends Process
{
    /**
     * worker id.
     *
     * @var integer
     */
    protected int $workerId;

    /**
     * WorkerKeeper Constructor,
     *
     * @param integer $pid
     * @param integer $workerId
     * @return void
     */
    public function __construct(Manager $manager,int $pid,int $workerId)
    {
        parent::__construct($manager,$pid);
        $this->workerId = $workerId;
    }

    /**
     * get worker id.
     *
     * @return integer
     */
    public function getWorkerId(): int
    {
        return $this->workerId;
    }
}