<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Process;

use Larmias\Engine\Swoole\Process\Signal\PcntlSignalHandler;
use Larmias\Engine\Swoole\Process\Worker\WorkerKeeper;
use Larmias\Engine\Swoole\Constants;
use Swoole\Process as SwooleProcess;
use ErrorException;
use Closure;
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
     * @var int
     */
    protected int $status = Constants::STATUS_NORMAL;

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
        $workerConfig = $this->getWorkerConfig($workerId);
        $process = new SwooleProcess(fn(SwooleProcess $p) => $this->handle($p, $workerId), false, 0, $workerConfig['enable_coroutine']);
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
            $waitResult = SwooleProcess::wait();
            if ($waitResult) {
                $worker = $this->workers[$waitResult['pid']];
                unset($this->workers[$waitResult['pid']]);
                $worker->setFromWaitResult($waitResult);
                $this->workerExitHandler($worker);
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
     * @throws ErrorException
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
        if (($this->status == Constants::STATUS_NORMAL && $workerRecover) || $this->status == Constants::STATUS_RELOAD) {
            $this->forkOneWorker($worker->getId());
        }

        if ($this->status == Constants::STATUS_RELOAD && count($this->workers) == $this->workerNum) {
            $this->status = Constants::STATUS_NORMAL;
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
        if ($this->status != Constants::STATUS_NORMAL) {
            $this->log('Master signal processing..., receive signal = %d', $signalNo);
            return;
        }

        if ($signalNo == SIGUSR1) {
            $this->status = Constants::STATUS_RELOAD;
        } else {
            $this->status = Constants::STATUS_STOP;
        }

        $this->stopAllWorker();
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
     * 停止全部的worker
     * @return bool
     */
    protected function stopAllWorker(): bool
    {
        foreach ($this->workers as $worker) {
            $worker->stop();
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

        $message = sprintf(trim($format), ...$args);
        $message = sprintf("%s pid:%d %s\n", date('Y-m-d H:i:s'), $this->getFileMasterPid(), $message);

        if (!$this->config['daemonize']) {
            fwrite(STDOUT, $message);
            fflush(STDOUT);
        }

        if ($this->config['log_file']) {
            file_put_contents($this->config['log_file'], $message, FILE_APPEND | LOCK_EX);
        }
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
     * 获取worker配置
     * @param int $workerId
     * @return array
     */
    public function getWorkerConfig(int $workerId): array
    {
        $workerConfig = $this->config['worker_config'] ?? [];

        if ($workerConfig instanceof Closure) {
            $workerConfig = $workerConfig($workerId);
        }

        return array_merge($this->config, $workerConfig);
    }

    /**
     * 获取默认配置
     * @return array
     */
    protected static function getDefaultConfig(): array
    {
        return [
            'log_debug' => false,
            'log_file' => null,
            'daemonize' => false,
            'worker_num' => 1,
            'enable_coroutine' => true,
            'max_wait_time' => 3,
            'stop_wait_time' => 3,
            'worker_auto_recover' => true,
            'pid_file' => null,
            'worker_config' => null,
        ];
    }
}