<?php

declare(strict_types=1);

namespace Larmias\WorkerS\Process;

use Larmias\WorkerS\Concerts\HasEvents;
use Larmias\WorkerS\Process\Contracts\SignalInterface;
use Larmias\WorkerS\Support\Helper;

class Signal
{
    use HasEvents;

    /**
     * 是否打开异步信号
     * 
     * @var boolean
     */
    protected bool $openAsyncSignal = false;

    /**
     * @var SignalInterface|null
     */
    protected ?SignalInterface $signalHandler = null;

    /**
     * linux signal list. 
     *
     * @var array
     */
    protected static $signalSupport = [];

    /**
     * @var boolean
     */
    protected bool $isMaster = false;
    
    /**
     * Signal init.
     * 
     * @return void
     */
    public static function init(): void
    {
        if (Helper::isUnix()) {
            static::$signalSupport = [
                'usr1'      => \SIGUSR1,
                'usr2'	    => \SIGUSR2,
                'quit'      => \SIGQUIT,
                'int'		=> \SIGINT,
                'term'      => \SIGTERM,
                'hup'       => \SIGHUP,
                'tstp'      => \SIGTSTP,
                'iot'       => \SIGIOT,
                'gio'       => \SIGIO,
            ];
        }
    }

    /**
     * 开启异步信号
     * 
     * @param  bool $async
     * @return void
     */
    public function openAsyncSignal(bool $async = false): void
    {
        if (!Helper::isUnix() || $this->signalHandler !== null) {
            return;
        }
        if (!Helper::isSupportAsyncSignal()) {
            $async = false;
        }
        $this->openAsyncSignal = $async;
        \pcntl_async_signals($async);
    }

    /**
     * 注册信号监听
     *
     * @return void
     */
    public function register(): void
    {
        if (!Helper::isUnix()) {
            return;
        }
        foreach (static::$signalSupport as $signal) {
            if ($this->signalHandler !== null) {
                \pcntl_signal($signal,\SIG_IGN,false);
                $this->signalHandler->onSignal($signal,[$this,'triggerSignal']);
            } else {
                \pcntl_signal($signal,[$this,'triggerSignal'],!$this->isMaster);
            }
        }
        if ($this->isMaster) {
            $this->signalHandler !== null ? $this->signalHandler->offSignal(\SIGPIPE) : \pcntl_signal(\SIGPIPE, \SIG_IGN, false);
        }
    }

    /**
     * 触发信号监听
     *
     * @param integer $signal
     * @return void
     */
    public function triggerSignal(int $signal): void
    {
        if (empty(static::$signalSupport)) {
            return;
        }
        $event = \array_search($signal,static::$signalSupport,true);
        $this->fireEvent($event,$signal);
    }

    /**
     * @param SignalInterface $signalHandler
     * @return self
     */
    public function setSignalHandler(SignalInterface $signalHandler): self
    {
        $this->signalHandler = $signalHandler;
        return $this;
    }

    /**
     * dispatch signal for the handlers
     *
     * @return void
     */
    public function dispatch(): void
    {
        if (!Helper::isUnix()) {
            return;
        }
        if ($this->signalHandler !== null) {
            $this->signalHandler->dispatchSignal();
        } else {
            !$this->openAsyncSignal && \pcntl_signal_dispatch();
        }
    }

    /**
     * @return boolean
     */
    public function isMaster(): bool
    {
        return $this->isMaster;
    }

    /**
     * @param boolean $isMaster
     * @return self
     */
    public function setIsMaster(bool $isMaster = false): self
    {
        $this->isMaster = $isMaster;
        return $this;
    }
}

Signal::init();