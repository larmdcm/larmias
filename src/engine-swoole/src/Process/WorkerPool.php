<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Process;

use Larmias\Engine\Swoole\Process\Signal\PcntlSignalHandler;
use Larmias\Engine\Swoole\Process\Worker\WorkerKeeper;
use Larmias\Engine\Swoole\Process\Worker\Worker as BaseWorker;
use Swoole\Process as SwooleProcess;
use function array_merge;
use function max;

class WorkerPool
{
    /**
     * @var WorkerKeeper[]
     */
    protected array $workers = [];

    /**
     * @var array
     */
    protected array $events = [];

    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var int
     */
    protected int $workerNum = 1;

    /**
     * @var int
     */
    protected int $masterPid = 0;

    /**
     * @param int $workerNum
     */
    public function __construct(int $workerNum = 1)
    {
        $this->config = static::getDefaultConfig();
        $this->config['worker_num'] = $workerNum;
    }

    /**
     * 初始化
     * @return self
     */
    public function initialize(): self
    {
        $this->workerNum = max(1, $this->config['worker_num']);
        return $this;
    }

    /**
     * 进程守护化
     * @return self
     */
    public function daemonize(): self
    {
        if ($this->config['daemonize']) {
            SwooleProcess::daemon(true, false);
        }
        return $this;
    }

    /**
     * 监听事件
     * @param string $name
     * @param callable $callback
     * @return void
     */
    public function on(string $name, callable $callback): void
    {
        $this->events[$name] = $callback;
    }

    /**
     * 设置配置
     * @param array $config
     * @return void
     */
    public function set(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * 启动
     * @return void
     */
    public function start(): void
    {
        $this->initialize()
            ->daemonize()
            ->forkWorkers()
            ->process();
    }

    /**
     * @return self
     */
    protected function registerSignalHandler(): self
    {
        SignalManager::setSignalHandler(new PcntlSignalHandler());
        SignalManager::on([SIGUSR1, SIGTERM, SIGINT, SIGQUIT, SIGHUP, SIGTSTP], function (int $signalNo) {
            $this->handleSignal($signalNo);
        });
        return $this;
    }

    /**
     * @return self
     */
    protected function forkWorkers(): self
    {
        for ($i = 0; $i < $this->workerNum; $i++) {
            $this->forkOneWorker($i);
        }
        return $this;
    }

    /**
     * @param int $workerId
     * @return self
     */
    protected function forkOneWorker(int $workerId): self
    {
        $process = new SwooleProcess(fn(SwooleProcess $p) => $this->handle($p, $workerId), false, 0, true);
        $pid = $process->start();
        $this->workers[$pid] = new WorkerKeeper($this, $process, $workerId);
        return $this;
    }

    /**
     * @return void
     */
    protected function process(): void
    {
        if (!$this->masterPid) {
            $this->setMasterPid();
        }
        $this->registerSignalHandler();
        while (true) {
            $waitResult = SwooleProcess::wait(true);
            if ($waitResult) {
                $worker = $this->workers[$waitResult['pid']];
                $worker->setFromWaitResult($waitResult);
                $this->workerExitHandler($worker);
                unset($this->workers[$waitResult['pid']]);
            }
            if (empty($this->workers)) {
                $this->exit();
                return;
            }
        }
    }

    /**
     * @param SwooleProcess $process
     * @param int $workerId
     * @return void
     */
    protected function handle(SwooleProcess $process, int $workerId): void
    {
        $worker = new Worker($this, $process, $workerId);
        $worker->process();
    }

    /**
     * @param WorkerKeeper $worker
     * @return void
     */
    protected function workerExitHandler(WorkerKeeper $worker): void
    {
        $exitCode = $worker->getExitCode();
        $this->log('Worker exit,code = %d,signal = %d', $exitCode, $worker->getSignal());
        $workerRecover = $this->config['worker_auto_recover'];
        if ($exitCode == BaseWorker::EXIT_RELOAD || ($workerRecover && $exitCode != BaseWorker::EXIT_STOP)) {
            $this->forkOneWorker($worker->getId());
        }
    }

    /**
     * 信号处理
     * @param int $signalNo
     * @return void
     */
    protected function handleSignal(int $signalNo): void
    {
        $this->log('Master receiving signal,signal = %d', $signalNo);
        if ($signalNo == SIGUSR1) {
            $this->command(BaseWorker::EXIT_RELOAD);
        } else {
            $this->command(BaseWorker::EXIT_STOP);
        }
    }

    /**
     * 停止进程
     * @return bool
     */
    public function stop(): bool
    {
        $masterPid = $this->getMasterPid();
        if (!$masterPid) {
            return false;
        }
        $stopTime = time();
        $stopWaitTime = $this->config['stop_wait_time'] + $this->config['max_wait_time'];
        SwooleProcess::kill($masterPid, SIGTERM);
        while (true) {
            if (SwooleProcess::kill($masterPid, 0)) {
                if (time() - $stopTime > $stopWaitTime) {
                    return false;
                }
                sleep(1);
            } else {
                return true;
            }
        }
    }

    /**
     * 重启进程
     * @return bool
     */
    public function reload(): bool
    {
        $masterPid = $this->getMasterPid();
        if (!$masterPid) {
            return false;
        }
        SwooleProcess::kill($masterPid, SIGUSR1);
        return true;
    }

    /**
     * @param int $code
     * @return bool
     */
    public function command(int $code): bool
    {
        foreach ($this->workers as $worker) {
            match ($code) {
                BaseWorker::EXIT_RELOAD => $worker->reload(),
                default => $worker->stop(),
            };
        }

        return true;
    }

    /**
     * @param string $name
     * @param ...$args
     * @return void
     */
    public function trigger(string $name, ...$args): void
    {
        if (isset($this->events[$name])) {
            call_user_func($this->events[$name], ...$args);
        }
    }

    /**
     * @param string $name
     * @param mixed|null $default
     * @return mixed
     */
    public function getConfig(string $name, mixed $default = null): mixed
    {
        return $this->config[$name] ?? $default;
    }

    /**
     * 日志
     * @param string $format
     * @param ...$args
     * @return void
     */
    public function log(string $format, ...$args): void
    {
        if (!$this->config['log_debug']) {
            return;
        }

        printf($format . PHP_EOL, ...$args);
    }

    /**
     * get master pid.
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
     * @return self
     */
    public function setMasterPid(): self
    {
        $this->masterPid = getmypid();
        file_put_contents($this->getMasterPidPath(), $this->masterPid);
        return $this;
    }

    /**
     * @return int
     */
    public function getFileMasterPid(): int
    {
        $path = $this->getMasterPidPath();
        return file_exists($path) ? intval(file_get_contents($path)) : 0;
    }

    /**
     * @return string
     */
    public function getMasterPidPath(): string
    {
        if (!$this->config['pid_file']) {
            $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
            $file = str_replace('/', '_', end($backtrace)['file']) . '.pid';
            $this->config['pid_file'] = sys_get_temp_dir() . DIRECTORY_SEPARATOR . $file;
        }
        return $this->config['pid_file'];
    }

    /**
     * 退出
     * @return void
     */
    public function exit(): void
    {
        file_exists($this->getMasterPidPath()) && unlink($this->getMasterPidPath());
    }

    /**
     * 获取默认配置
     * @return array
     */
    protected static function getDefaultConfig(): array
    {
        return [
            'log_debug' => false,
            'daemonize' => false,
            'worker_num' => 1,
            'enable_coroutine' => true,
            'max_wait_time' => 3,
            'stop_wait_time' => 3,
            'worker_auto_recover' => true,
            'pid_file' => null,
        ];
    }
}