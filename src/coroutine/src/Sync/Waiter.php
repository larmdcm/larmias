<?php

declare(strict_types=1);

namespace Larmias\Coroutine\Sync;

use Closure;
use Larmias\Contracts\Sync\WaiterInterface;
use Larmias\Coroutine\Coroutine;
use Larmias\Coroutine\ChannelFactory;
use Larmias\Coroutine\Exceptions\ExceptionThrower;
use Larmias\Contracts\Sync\WaitTimeoutException;
use Throwable;

class Waiter implements WaiterInterface
{
    protected float $pushTimeout = 10.0;

    protected float $popTimeout = 10.0;

    public function __construct(float $timeout = 10.0)
    {
        $this->popTimeout = $timeout;
    }

    /**
     * @param Closure $closure
     * @param float|null $timeout
     * @return mixed
     * @throws Throwable
     */
    public function wait(Closure $closure, ?float $timeout = null): mixed
    {
        if ($timeout === null) {
            $timeout = $this->popTimeout;
        }

        $channel = ChannelFactory::make();
        Coroutine::create(function () use ($channel, $closure) {
            try {
                $result = $closure();
            } catch (Throwable $exception) {
                $result = new ExceptionThrower($exception);
            } finally {
                $channel->push($result ?? null, $this->pushTimeout);
            }
        });

        $result = $channel->pop($timeout);
        if ($result === false && $channel->isTimeout()) {
            throw new WaitTimeoutException(sprintf('Channel wait failed, reason: Timed out for %s s', $timeout));
        }
        if ($result instanceof ExceptionThrower) {
            throw $result->getThrowable();
        }

        return $result;
    }
}
