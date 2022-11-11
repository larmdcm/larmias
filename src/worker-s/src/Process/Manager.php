<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process;

use Larmias\WorkerS\Concerts\HasEvents;
use Larmias\WorkerS\Process\Constants\Event as EventConstant;
use Larmias\WorkerS\Support\Helper;
use Larmias\WorkerS\Process\Worker\{Process, Worker,WorkerKeeper};
use Larmias\WorkerS\Process\Exceptions\ProcessException;
use Larmias\WorkerS\Support\Arr;

use Throwable;

class Manager
{
    use HasEvents;

    /**
     * @var int
     */
    public const COMMAND_WORKER_RELOAD = 1;

    /**
     * @var int
     */
    public const COMMAND_WORKER_STOP = 2;

    /**
     * @var int
     */
    public const COMMAND_WORKER_CUSTOM = 3;

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var string
     */
    protected string $name;

    /**
     * @var int
     */
    protected int $masterPid = 0;

    /**
     * @var int
     */
    protected int $workerNum;

    /**
     * @var int
     */
    protected int $loopMicrotime;

    /**
     * @var WorkerKeeper[]
     */
    protected array $workers = [];

    /**
     * @var Worker|null
     */
    protected ?Worker $masterWorker = null;

    /**
     * @var Signal
     */
    protected Signal $signal;

    /**
     * @var boolean
     */
    protected bool $isExit = false;

    /**
     * @var int
     */
    protected int $exitSignal = 0;

    /**
     * @var boolean
     */
    protected bool $isInit = false;

    /**
     * Manager Constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = \array_merge($this->config,static::getDefaultConfig(),$config);
        $this->signal = new Signal();
    }

    /**
     * 初始化
     *
     * @return self
     */
    public function init(): self
    {
        if ($this->isInit) {
            return $this;
        }
        $this->workerNum     = \max(1,$this->config['worker_num'] ?? 1);
        $this->loopMicrotime = $this->config['loop_microtime'];
        $this->name          = $this->config['name'] ?? 'worker-s';

        if (!$this->config['runtime_path']) {
            $this->config['runtime_path'] = Helper::getRuntimePath();
        }
        $this->isInit = true;
        return $this;
    }

    /**
     * @return int
     * @throws Throwable
     */
    public function run(): int
    {
        $this->init()
             ->daemonize()
             ->forkWorkers()
             ->registerSignalHandler()
             ->process();

        return $this->exitSignal;
    }

    /**
     * 守护进程方式运行
     *
     * @return self
     */
    protected function daemonize(): self
    {
        if ($this->config['daemonize']) {
            Daemonize::run();
        }
        return $this;
    }

    /**
     *
     * @throws \Throwable
     * @return self
     */
    protected function forkWorkers(): self
    {
        for ($i = 1; $i <= $this->workerNum; $i++) {
            $this->forkOneWorker($i);
        }
        return $this;
    }

    /**
     * @param int $workerId
     * @return void
     * @throws Throwable
     */
    protected function forkOneWorker(int $workerId = -1): void
    {
        if (!Helper::isUnix()) {
            return;
        }
        $pid = \pcntl_fork();
        switch ($pid) {
            // fork process error.
            case -1:
                throw new ProcessException('fork process error.');
                break;
            // child process.
            case 0:
                try {
                    $worker = new Worker($this,$workerId);
                    $this->fireEvent(EventConstant::ON_WORKER_START,$worker);
                    $worker->process();
                    exit(0);
                } catch (Throwable $e) {
                    throw $e;
                }
                break;
            // parent process.
            default:
                $workerKeeper = new WorkerKeeper($this,$pid,$workerId);
                $this->workers[$workerKeeper->getPid()] = $workerKeeper;
                if (!$this->masterPid) {
                    $this->setMasterPid();
                }
                break;
        }
    }

    /**
     * @return self
     */
    protected function registerSignalHandler(): self
    {
        $this->signal->setIsMaster(true);

        $command = function (int $signal,...$args) {
            $this->fireEvent(EventConstant::ON_MASTER_SIGNAL,$this,$signal);
            $this->exitSignal = $signal;
            $this->command(...$args);
        };

        $this->signal->on('usr1',function ($signal) use ($command) {
            $command($signal);
        });

        $this->signal->on('usr2',function () {
            $this->fireEvent(EventConstant::ON_MASTER_CUSTOM,$this);
            $this->command(self::COMMAND_WORKER_CUSTOM);
        });
        
        $this->signal->on('quit',function ($signal) use ($command) {
            $command($signal,self::COMMAND_WORKER_STOP);
        });
        
        $this->signal->on('int,term,hup,tstp',function ($signal) use ($command) {
            $command($signal,self::COMMAND_WORKER_STOP,true);
        });

        $this->signal->register();

        return $this;
    }

    /**
     * @return void
     * @throws Throwable
     */
    protected function process(): void
    {
        if (!$this->isMaster()) {
            return;
        }
        
        $this->fireEvent(EventConstant::ON_MASTER_START,$this);
        $this->signal->openAsyncSignal($this->config['open_async_signal'] ?? true);

        while (true) {
            if (Helper::isUnix()) {
                $this->signal->dispatch();
                $workerPid = \pcntl_wait($status,$this->hasListen('master') ? \WNOHANG : \WUNTRACED);
                $this->signal->dispatch();
                if ($workerPid > 0) {
                    $exitCode = \intval(\pcntl_wexitstatus($status));
                    $this->workerExitHandler($this->workers[$workerPid],$exitCode);
                    unset($this->workers[$workerPid]);
                }
            } else {
                $this->getMasterWorker()->execute();
            }

            if ($this->isExit && empty($this->workers)) {
                $this->exit();
                return;
            }
            
            $this->fireEvent(EventConstant::ON_MASTER,$this);

            usleep($this->loopMicrotime);
        }
    }

    /**
     * @param WorkerKeeper $workerKeeper
     * @param int $exitCode
     * @return void
     * @throws Throwable
     */
    protected function workerExitHandler(WorkerKeeper $workerKeeper,int $exitCode): void
    {
        switch ($exitCode) {
            case Process::STATUS_RELOAD:
                $this->forkOneWorker($workerKeeper->getWorkerId());
                break;
            case Process::STATUS_STOP:
                break;
            default:
                $autoRestart = $this->config['worker_auto_recover'] ?? true;
                if ($autoRestart) {
                    $this->forkOneWorker($workerKeeper->getWorkerId());
                }
                break;
        }
    }

    /**
     * @return Worker
     */
    public function getMasterWorker(): Worker
    {
        if (!$this->masterWorker) {
            $this->masterWorker = new Worker($this,1);
            $this->fireEvent(EventConstant::ON_WORKER_START,$this->masterWorker);
        }
        return $this->masterWorker;
    }

    /**
     * @param int     $workerCommand
     * @param boolean $masterStop
     * @return bool
     */
    public function command(int $workerCommand = self::COMMAND_WORKER_RELOAD,bool $masterStop = false): bool
    {
        if ($masterStop) {
            $workerCommand = self::COMMAND_WORKER_STOP;
        }
        foreach ($this->workers as $workerKeeper) {
            switch ($workerCommand) {
                case self::COMMAND_WORKER_RELOAD:
                    $workerKeeper->reload();
                    break;
                case self::COMMAND_WORKER_STOP:
                    $workerKeeper->stop();
                    break;
                case self::COMMAND_WORKER_CUSTOM:
                    $workerKeeper->custom();
                    break;
            }
        }
        if ($masterStop) {
            $this->isExit = true;
        }
        return true;
    }

    /**
     * reload
     * 
     * @param  bool $killSignal
     * @return bool
     */
    public function reload(bool $killSignal = true): bool
    {
        if ($killSignal) {
            return \posix_kill($this->getMasterPid(),\SIGUSR1);
        }
        return $this->command();
    }

    /**
     * stop 
     *
     * @param  bool $killSignal
     * @return bool
     */
    public function stop(bool $killSignal = true): bool
    {
        if ($killSignal) {
            $masterPid = $this->getMasterPid();
            $isStop = \posix_kill($masterPid,\SIGTERM);
            if (!$isStop) {
                return false;
            }
            $stopTime = \time();
            $stopWaitTime = $this->config['stop_wait_time'];
            while (true) {
                if (\posix_kill($masterPid,0)) {
                    if (time() - $stopTime > $stopWaitTime) {
                        return false;
                    }
                    sleep(1);
                } else {
                    return true;
                }
            }
        }
        return $this->command(self::COMMAND_WORKER_STOP,true);
    }
    
    /**
     * @return void
     */
    public function exit(): void
    {
        \file_exists($this->getMasterPidPath()) && \unlink($this->getMasterPidPath());
        $this->fireEvent(EventConstant::ON_MASTER_STOP,$this);
        exit(0);
    }

    /**
     * 设置配置.
     *
     * @param string|array $name
     * @param mixed $value
     * @return self
     */
    public function setConfig(string|array $name,$value = null): self
    {
        if (is_array($name)) {
            $this->config = \array_merge($this->config,$name);
        } else {
            Arr::set($this->config,$name,$value);
        }
        return $this;
    }

    /**
     * 获取配置
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function getConfig(string $name,$default = null): mixed
    {
        return Arr::get($this->config,$name,$default);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->config['name'];
    }

    /**
     * 是否为master进程
     *
     * @return boolean
     */
    public function isMaster(): bool
    {
        if (!Helper::isUnix()) {
            return true;
        }
        return $this->getMasterPid() === \getmypid();
    }

    /**
     * get master pid.
     *
     * @return int
     */
    public function getMasterPid(): int
    {
        if (!$this->masterPid) {
            $this->masterPid = $this->getFileMasterPid();
        }
        return $this->masterPid;
    }
    
    /**
     * set master pid.
     *
     * @return self
     */
    public function setMasterPid(): self
    {
        $this->masterPid = \getmypid();
        \file_put_contents($this->getMasterPidPath(),$this->masterPid);
        return $this;
    }

    /**
     * @return int
     */
    public function getFileMasterPid(): int
    {
        $path = $this->getMasterPidPath();
        return \file_exists($path) ? \intval(\file_get_contents($path)) : 0;
    }

    /**
     * @return string
     */
    public function getMasterPidPath(): string
    {
        return $this->getRuntimePath($this->name . '.master.pid');
    }

    /**
     * @return int
     */
    public function getLoopMicrotime(): int
    {
        return $this->loopMicrotime;
    }

    /**
     * @param  string $path
     * @return string
     */
    public function getRuntimePath(string $path = ''): string
    {
        return Helper::getDirPath($this->config['runtime_path']) . DIRECTORY_SEPARATOR . rtrim($path,DIRECTORY_SEPARATOR);
    }

    /**
     * @return Signal
     */
    public function getSignal(): Signal
    {
        return $this->signal;
    }

    /**
     * 获取默认配置
     *
     * @return array
     */
    public static function getDefaultConfig(): array
    {
        return [
            'name'                => 'worker-s',
            'worker_num'          => 1,
            'loop_microtime'      => 10000,
            'stop_wait_time'      => 5,
            'daemonize'           => false,
            'runtime_path'        => null,
            'open_async_signal'   => true,
            'worker_memory_limit' => -1,
            'worker_auto_recover' => false,
        ];
    }
}