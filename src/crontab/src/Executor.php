<?php

declare(strict_types=1);

namespace Larmias\Crontab;

use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LockerFactoryInterface;
use Larmias\Crontab\Contracts\ExecutorInterface;
use Closure;
use Larmias\Contracts\TimerInterface;
use Throwable;
use function is_callable;
use function is_string;
use function explode;
use function time;
use function method_exists;

abstract class Executor implements ExecutorInterface
{
    /**
     * @param ContainerInterface $container
     * @param LockerFactoryInterface $lockerFactory
     * @param TimerInterface $timer
     */
    public function __construct(protected ContainerInterface $container, protected LockerFactoryInterface $lockerFactory, protected TimerInterface $timer)
    {
        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * @param Crontab $crontab
     * @return void
     */
    public function handle(Crontab $crontab): void
    {
        if (!$crontab->getExecuteTime()) {
            return;
        }
        $this->runCallback($crontab);
    }

    /**
     * @param Crontab $crontab
     * @param Closure $callback
     * @return Closure
     */
    protected function runInSingleton(Crontab $crontab, Closure $callback): Closure
    {
        return function () use ($crontab, $callback) {
            $locker = $this->lockerFactory->create($crontab->getMutexName(), $crontab->getMutexExpires());
            if (!$locker->acquire()) {
                return;
            }
            try {
                $callback();
            } finally {
                $locker->release();
            }
        };
    }

    /**
     * @param Crontab $crontab
     * @param Closure $callback
     * @return Closure
     */
    protected function runOnOneServer(Crontab $crontab, Closure $callback): Closure
    {
        return function () use ($crontab, $callback) {
            $locker = $this->lockerFactory->create('mutex:crontab' . \sha1($crontab->getName() . $crontab->getRule()), $crontab->getMutexExpires());
            if (!$locker->acquire()) {
                return;
            }
            try {
                $callback();
            } finally {
                $locker->release();
            }
        };
    }

    /**
     * @param Crontab $crontab
     * @return void
     */
    protected function runCallback(Crontab $crontab): void
    {
        $diff = $crontab->getExecuteTime() - time();
        $this->timer->after($diff > 0 ? $diff * 1000 : 1, function () use ($crontab) {
            $callback = $this->getCallback($crontab);
            if ($crontab->isSingleton()) {
                $callback = $this->runInSingleton($crontab, $callback);
            }
            if ($crontab->isOnOneServer()) {
                $callback = $this->runOnOneServer($crontab, $callback);
            }
            $callback();
        });
    }

    /**
     * @param Crontab $crontab
     * @return Closure
     */
    protected function getCallback(mixed $crontab): Closure
    {
        return function () use ($crontab) {
            $args = [];
            $handler = $crontab->getHandler();
            if (!is_callable($handler)) {
                if (is_string($handler)) {
                    $handler = explode('@', $handler);
                }
                $instance = $this->container->make($handler[0], [], true);
                $handler = [$instance, $handler[1]];
                $args = $handler[2] ?? [];
            }
            try {
                $result = $this->container->invoke($handler, $args);
                if (isset($instance) && method_exists($instance, 'onFinish')) {
                    $instance->onFinish($crontab, $result);
                }
            } catch (Throwable $e) {
                if (isset($instance) && method_exists($instance, 'onException')) {
                    $instance->onException($crontab, $e);
                }
            }
        };
    }
}