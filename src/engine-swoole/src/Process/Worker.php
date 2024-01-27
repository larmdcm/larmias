<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Process;

use Larmias\Engine\Swoole\ProcessManager;
use Larmias\Engine\Swoole\SignalHandler;
use Swoole\Constant;
use Swoole\Runtime;
use Larmias\Engine\Swoole\Process\Worker\Worker as BaseWorker;
use Swoole\Timer;

class Worker extends BaseWorker
{
    /**
     * @var int
     */
    protected int $maxWaitTime = 0;

    /**
     * 启动进程
     * @return void
     */
    public function process(): void
    {
        Runtime::enableCoroutine($this->workerPool->getConfig('enable_coroutine'));
        $this->maxWaitTime = $this->workerPool->getConfig('max_wait_time');
        $this->registerSignalHandler();
        $this->workerPool->trigger(Constant::EVENT_WORKER_START, $this);
        $this->exit();
    }


    /**
     * @return self
     */
    protected function registerSignalHandler(): self
    {
        SignalManager::setSignalHandler(new SignalHandler());
        SignalManager::offAll();
        SignalManager::on([SIGUSR1, SIGTERM, SIGQUIT, SIGHUP, SIGTSTP], function (int $signalNo) {
            $this->handleSignal($signalNo);
        });
        SignalManager::on([SIGINT], fn() => null);
        return $this;
    }

    /**
     * 信号处理
     * @param int $signalNo
     * @return void
     */
    protected function handleSignal(int $signalNo): void
    {
        $this->workerPool->log('Worker#%d receiving signal,signal = %d', $this->id, $signalNo);

        if ($signalNo == SIGUSR1) {
            $this->exitCode = self::EXIT_RELOAD;
        } else if ($signalNo == SIGTERM) {
            $this->exitCode = self::EXIT_STOP;
        } else {
            $this->exitCode = self::EXIT_NORMAL;
        }

        if ($this->maxWaitTime > 0) {
            ProcessManager::setRunning(false);
            Timer::after($this->maxWaitTime * 1000, function () {
                $this->workerPool->log('Worker#%d exit timeout, forced exit', $this->id);
                $this->exit();
            });
        } else {
            $this->exit();
        }
    }
}