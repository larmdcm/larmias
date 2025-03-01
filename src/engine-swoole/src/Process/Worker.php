<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Process;

use Larmias\Contracts\SignalHandlerInterface;
use Larmias\Engine\Swoole\Process\Timer\AlarmTimer;
use Larmias\Engine\Swoole\Process\Worker\Worker as BaseWorker;
use Larmias\Engine\Swoole\Process\Signal\PcntlSignalHandler;
use Larmias\Contracts\TimerInterface;
use Larmias\Engine\Swoole\ProcessManager;
use Larmias\Engine\Swoole\SignalHandler;
use Larmias\Engine\Swoole\Constants;
use Larmias\Engine\Swoole\Timer;
use Swoole\Constant;
use Swoole\Coroutine;
use Swoole\Runtime;
use ErrorException;

class Worker extends BaseWorker
{
    /**
     * @var array
     */
    protected array $workerConfig = [];

    /**
     * @var int
     */
    protected int $maxWaitTime = 0;

    /**
     * @var bool
     */
    protected bool $enableCoroutine;

    /**
     * @var TimerInterface
     */
    protected TimerInterface $timer;

    /**
     * 启动进程
     * @return void
     * @throws ErrorException
     */
    public function process(): void
    {
        $this->workerConfig = $this->workerPool->getWorkerConfig($this->id);
        $this->enableCoroutine = $this->workerConfig['enable_coroutine'];
        $this->maxWaitTime = $this->workerConfig['max_wait_time'];
        Runtime::enableCoroutine($this->enableCoroutine);
        $signalHandler = $this->enableCoroutine ? new SignalHandler() : new PcntlSignalHandler();
        $this->timer = $this->enableCoroutine ? new Timer() : new AlarmTimer($signalHandler);
        $this->registerSignalHandler($signalHandler);
        $this->workerPool->trigger(Constant::EVENT_WORKER_START, $this);
        $this->timer->clear();
    }


    /**
     * @param SignalHandlerInterface $signalHandler
     * @return self
     */
    protected function registerSignalHandler(SignalHandlerInterface $signalHandler): self
    {
        SignalManager::setSignalHandler($signalHandler);
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

        ProcessManager::setRunning(false);

        $waitTime = $this->maxWaitTime * 1000;

        $callback = function () {
            $this->workerPool->log('Worker#%d exit timeout, forced exit', $this->id);
            foreach (Coroutine::listCoroutines() as $coroutine) {
                Coroutine::cancel($coroutine);
            }
            $this->exit(Constants::EXIT_CODE_FAIL);
        };

        if ($waitTime > 0) {
            $this->timer->after($waitTime, $callback);
        }

        $this->timer->clear();
    }
}