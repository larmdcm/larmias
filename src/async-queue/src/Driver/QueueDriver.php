<?php

declare(strict_types=1);

namespace Larmias\AsyncQueue\Driver;

use Larmias\AsyncQueue\Contracts\JobHandlerInterface;
use Larmias\AsyncQueue\Contracts\MessageInterface;
use Larmias\AsyncQueue\Contracts\QueueDriverInterface;
use Larmias\AsyncQueue\Exceptions\QueueException;
use Larmias\AsyncQueue\Job;
use Larmias\Contracts\Concurrent\ConcurrentInterface;
use Larmias\Contracts\Concurrent\ParallelInterface;
use Larmias\Contracts\ContainerInterface;
use Larmias\Contracts\ContextInterface;
use Larmias\Contracts\LoggerInterface;
use Larmias\Contracts\PackerInterface;
use Larmias\Contracts\TimerInterface;
use Larmias\Support\Packer\PhpSerializerPacker;
use Throwable;
use function array_merge;
use function Larmias\Support\format_exception;
use function Larmias\Support\throw_unless;
use function method_exists;
use function sleep;

abstract class QueueDriver implements QueueDriverInterface
{
    /**
     * @var array
     */
    protected array $config = [];

    /**
     * @var PackerInterface
     */
    protected PackerInterface $packer;

    /**
     * @var string|null
     */
    protected ?string $queue = null;

    /**
     * @var float|null
     */
    protected ?float $waitTime = null;

    protected ?ConcurrentInterface $concurrent = null;

    /**
     * @param ContainerInterface $container
     * @param ContextInterface $context
     * @param TimerInterface $timer
     * @param LoggerInterface|null $logger
     * @param array $config
     */
    public function __construct(
        protected ContainerInterface $container,
        protected ContextInterface   $context,
        protected TimerInterface     $timer,
        protected ?LoggerInterface   $logger = null,
        array                        $config = []
    )
    {
        $this->config = array_merge($this->config, $config);
        $this->packer = new PhpSerializerPacker();
        $this->concurrent = $this->getConcurrent();
        if (method_exists($this, 'initialize')) {
            $this->container->invoke([$this, 'initialize']);
        }
    }

    /**
     * @param MessageInterface $message
     * @param int $delay
     * @return MessageInterface|null
     */
    public function reload(MessageInterface $message, int $delay = 0): ?MessageInterface
    {
        if ($this->ack($message)) {
            $message->setMessageId('');
            return $this->push($message, $delay);
        }

        return null;
    }

    /**
     * @param string|null $queue
     * @param float|null $waitTime
     * @return void
     */
    public function consumer(?string $queue = null, ?float $waitTime = null): void
    {
        $this->queue = $queue;
        $this->waitTime = $waitTime;

        if ($this->context->inCoroutine()) {
            $this->coHandle();
        } else {
            $this->handle();
        }
    }

    /**
     * @return void
     */
    public function coHandle(): void
    {
        try {
            if ($this->concurrent) {
                $this->concurrent->create([$this, 'handle']);
            } else {
                /** @var ParallelInterface $parallel */
                $parallel = $this->container->make(ParallelInterface::class, [], true);
                $parallel->add([$this, 'handle']);
                $parallel->wait();
            }
        } catch (Throwable $e) {
            $this->logger?->error(format_exception($e));
        } finally {
            $this->timespan();
        }
    }

    /**
     * @return ConcurrentInterface|null
     */
    protected function getConcurrent(): ?ConcurrentInterface
    {
        if (!$this->context->inCoroutine()) {
            return null;
        }

        $concurrentLimit = $this->config['concurrent_limit'] ?? null;
        /** @var ConcurrentInterface|null $concurrent */
        $concurrent = null;

        if ($concurrentLimit) {
            $concurrent = $this->container->make(ConcurrentInterface::class, ['limit' => (int)$concurrentLimit], true);
        }

        return $concurrent;
    }


    /**
     * @return void
     */
    public function handle(): void
    {
        $message = $this->pop($this->waitTime ?? $this->config['wait_time'], $this->queue);
        if (!$message) {
            return;
        }

        try {
            /** @var JobHandlerInterface $handler */
            $handler = $this->container->make($message->getHandler());
            throw_unless($handler instanceof JobHandlerInterface, QueueException::class, 'handler not instanceof ' . JobHandlerInterface::class);
            $handler->handle(new Job($message, $this));
        } catch (Throwable $e) {
            $this->logger?->error(format_exception($e));
            if ($message->getAttempts() >= $message->getMaxAttempts()) {
                $this->fail($message);
            } else {
                $this->reload($message);
            }
        } finally {
            $this->timespan();
        }
    }

    /**
     * @param string|null $name
     * @return string
     */
    protected function getQueueKey(?string $name = null): string
    {
        $name = $name ?: $this->config['name'];
        return $this->config['prefix'] . $name;
    }

    /**
     * @return void
     */
    protected function timespan(): void
    {
        sleep($this->config['timespan']);
    }
}