<?php

declare(strict_types=1);

namespace Larmias\Engine\Swoole\Coroutine;

use Swoole\Coroutine;
use Swoole\Coroutine\WaitGroup;
use Swoole\Timer;
use Swoole\Coroutine\Channel;

class Waiter
{
    protected WaitGroup $waitGroup;

    protected Channel $doneCh;

    protected int $timerId = 0;

    public function __construct(protected int $timeout = 0)
    {
        $this->waitGroup = new WaitGroup();
        $this->doneCh = new Channel(1);

        Coroutine::create(function () {
            $this->doneCh->pop();
            if ($this->waitGroup->count() > 0) {
                if ($this->timeout > 0) {
                    $this->timerId = Timer::after($this->timeout * 1000, fn() => $this->doneAll());
                } else {
                    $this->doneAll();
                }
            }
        });
    }

    public function add(callable $callback): self
    {
        $this->waitGroup->add();
        Coroutine::create(function () use ($callback) {
            Coroutine::defer(function () {
                $this->waitGroup->count() > 0 && $this->waitGroup->done();
            });
            call_user_func($callback);
        });
        return $this;
    }

    public function done(): void
    {
        $this->doneCh->close();
    }

    protected function doneAll(): void
    {
        while ($this->waitGroup->count() > 0) {
            $this->waitGroup->done();
        }
    }

    public function wait(?callable $callback = null): void
    {
        $callback && call_user_func($callback);

        $this->waitGroup->wait();

        $this->timerId && Timer::clear($this->timerId);
    }
}