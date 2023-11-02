<?php

declare(strict_types=1);

namespace Larmias\Coroutine\Concurrent;

use Larmias\Contracts\Concurrent\ConcurrentInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Contracts\LoggerInterface;
use Larmias\Coroutine\ChannelFactory;
use Larmias\Coroutine\Coroutine;
use Throwable;
use function Larmias\Support\format_exception;

class Concurrent implements ConcurrentInterface
{
    protected ChannelInterface $channel;

    public function __construct(
        protected int              $limit = 0,
        protected ?LoggerInterface $logger = null,
    )
    {
        $this->channel = ChannelFactory::make($this->limit);
    }

    public function create(callable $callable): void
    {
        $this->channel->push(true);

        Coroutine::create(function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $e) {
                $this->logger?->error(format_exception($e));
            } finally {
                $this->channel->pop();
            }
        });
    }

    public function limit(): int
    {
        return $this->limit;
    }

    public function length(): int
    {
        return $this->channel->length();
    }

    public function getChannel(): ChannelInterface
    {
        return $this->channel;
    }
}