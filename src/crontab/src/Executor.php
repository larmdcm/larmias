<?php

declare(strict_types=1);

namespace Larmias\Crontab;

use Carbon\Carbon;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\LockerFactoryInterface;
use Larmias\Crontab\Contracts\ExecutorInterface;
use Closure;
use Larmias\Engine\Timer;

abstract class Executor implements ExecutorInterface
{
    /**
     * @param ContainerInterface $container
     * @param LockerFactoryInterface $lockerFactory
     */
    public function __construct(protected ContainerInterface $container, protected LockerFactoryInterface $lockerFactory)
    {
        if (\method_exists($this, 'initialize')) {
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
        $diff = $crontab->getExecuteTime()->diffInRealSeconds(new Carbon());
        Timer::after($diff > 0 ? $diff * 1000 : 1, function () use ($crontab) {
            $callback = $this->getCallback($crontab->getHandler());
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
     * @param mixed $handler
     * @return Closure
     */
    protected function getCallback(mixed $handler): Closure
    {
        return function () use ($handler) {
            $args = [];
            if (!\is_callable($handler)) {
                if (\is_string($handler)) {
                    $handler = \explode('@', $handler);
                }
                $instance = $this->container->make($handler[0], [], true);
                $handler = [$instance, $handler[1]];
                $args = $handler[2] ?? [];
            }
            $this->container->invoke($handler, $args);
        };
    }
}