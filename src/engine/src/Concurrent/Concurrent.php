<?php

declare(strict_types=1);

namespace Larmias\Engine\Concurrent;

use Larmias\Contracts\Concurrent\ConcurrentInterface;
use Larmias\Contracts\Coroutine\ChannelFactoryInterface;
use Larmias\Contracts\Coroutine\ChannelInterface;
use Larmias\Contracts\Coroutine\CoroutineInterface;
use Larmias\Contracts\LoggerInterface;
use Throwable;
use function Larmias\Utils\format_exception;

class Concurrent implements ConcurrentInterface
{
    protected ChannelInterface $channel;

    public function __construct(
        protected CoroutineInterface $coroutine,
        ChannelFactoryInterface      $channelFactory,
        protected ?LoggerInterface   $logger = null,
        protected int                $limit = 0)
    {
        $this->channel = $channelFactory->create($this->limit);
    }

    public function create(callable $callable): void
    {
        $this->channel->push(true);

        $this->coroutine->create(function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $e) {
                $this->logger->error(format_exception($e));
            } finally {
                $this->channel->pop();
            }
        });
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function getLength(): int
    {
        return $this->channel->length();
    }

    public function getChannel(): ChannelInterface
    {
        return $this->channel;
    }
}