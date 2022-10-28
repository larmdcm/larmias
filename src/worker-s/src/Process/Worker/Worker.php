<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process\Worker;

use Larmias\WorkerS\Process\Manager;
use Larmias\WorkerS\Process\Signal;

class Worker extends Process
{
    /**
     * 进程是否退出
     *
     * @var boolean
     */
    protected bool $isExit = false;

    /**
     * @var boolean
     */
    protected bool $forceExit = true;

    /**
     * worker id.
     *
     * @var integer
     */
    protected int $workerId;

    /**
     * @var integer
     */
    protected int $workerNo = -1;

    /**
     * @var Signal
     */
    protected Signal $signal;

    /**
     * @var integer
     */
    protected int $exitCode = 0;

    /**
     * Worker Constructor.
     *
     * @param Manager $manager
     * @param integer $workerId
     */
    public function __construct(Manager $manager,int $workerId)
    {
        parent::__construct($manager);
        $this->workerId = $workerId;
        $this->signal   = new Signal();
    }

    /**
     * @return void
     */
    public function process(): void
    {
        $this->registerSignalHandler();
        $this->signal->openAsyncSignal($this->manager->getConfig('open_async_signal',true));

        while (true) {
            $this->signal->dispatch();
            if ($this->isExit) {
                exit($this->exitCode);
            }
            if ($this->memoryExceeded()) {
                $this->restart();
            }
            $this->execute();
            usleep($this->manager->getLoopMicrotime());
        }
    }

    /**
     * @return void
     */
    public function execute(): void
    {
        $this->manager->fireEvent('worker',$this);
    }

    /**
     * @return void
     */
    public function registerSignalHandler(): void
    {
        $command = function (bool $isStop = false,int $signal =-1) {
            $this->manager->fireEvent('workerSignal',$this,$signal);
            $isStop ? $this->terminate() : $this->restart();
        };

        $this->signal->on('usr1',function ($signal) use ($command) {
            $command(false,$signal);
        });

        $this->signal->on('usr2',function () {
            $this->manager->fireEvent('workerCustom',$this);
        });
        
        $this->signal->on('quit,int,term,hup,tstp',function ($signal) use ($command) {
            $command(true,$signal);
        });

        $this->signal->register();
    }

    /**
     * 重启进程.
     *
     * @return void
     */
    public function restart(): void
    {
        $this->exitCode = Process::STATUS_RELOAD;
        $this->exit();
    }

    /**
     * 终止进程.
     *
     * @return void
     */
    public function terminate(): void
    {
        $this->exitCode = Process::STATUS_STOP;
        $this->exit();
    }

    /**
     * @return void
     */
    public function exit(): void
    {
        $this->isExit = true;
        $this->manager->fireEvent('workerStop',$this);
        if ($this->forceExit) {
            exit($this->exitCode);
        }
    }

    /**
     * @return boolean
     */
    public function isExit(): bool
    {
        return $this->isExit;
    }

    /**
     * @param  bool $forceExit
     * @return self
     */
    public function setForceExit(bool $forceExit): self
    {
        $this->forceExit = $forceExit;
        return $this;
    }

    /**
     * 超出内存限制判断
     *
     * @return bool
     */
    protected function memoryExceeded(): bool
    {
        $memoryLimit = $this->manager->getConfig('worker_memory_limit',-1);
        return $memoryLimit != -1 && (\memory_get_usage() / 1024 / 1024) > $memoryLimit;
    }

    /**
     * @return Signal
     */
    public function getSignal(): Signal
    {
        return $this->signal;
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

    /**
     * Get the value of workerNo
     *
     * @return  integer
     */
    public function getWorkerNo(): int
    {
        return $this->workerNo;
    }
    
    /**
     * Set the value of workerNo
     *
     * @param  integer  $workerNo  
     * @return self
     */
    public function setWorkerNo($workerNo): self
    {
        $this->workerNo = $workerNo;
        return $this;
    }
}